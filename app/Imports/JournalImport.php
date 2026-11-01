<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\Mark;
use App\Models\Schedule;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class JournalImport implements ToCollection
{
    protected $group;
    protected $subject;
    
    public $savedCount = 0;
    public $skippedStudents = [];
    public $skippedDates = [];
    public $skippedMarks = [];

    public function __construct($group, $subject)
    {
        $this->group = $group;
        $this->subject = $subject;
    }

    public function collection(Collection $rows): void
    {
        // 1. Загружаем студентов группы
        $validStudents = Student::where('group_id', $this->group->id)->get();

        // 2. Загружаем расписание
        $validSchedules = Schedule::where('group_id', $this->group->id)
            ->where('subject_id', $this->subject->id)
            ->get()
            ->keyBy(fn($schedule) => Carbon::parse($schedule->date)->format('Y-m-d') . '-' . $schedule->pair_number);

        // 3. Справочник оценок (приводим к нижнему регистру для надежности)
        $marks = Mark::all()->keyBy(fn($mark) => mb_strtolower(trim($mark->name)));

        $dateRow = $rows[0]; // Строка с датами
        $pairRow = $rows[1]; // Строка с парами

        $columnMap = []; 
        $lastValidDate = null; // Сюда будем сохранять последнюю найденную дату
        
        // УМНЫЙ ПЕРЕБОР КОЛОНОК
        for ($i = 0; $i < count($dateRow); $i++) {
            $rawDate = $dateRow[$i] ?? null;
            $rawPair = $pairRow[$i] ?? null;

            // 1. Ищем дату
            if (!empty($rawDate)) {
                $parsedDate = false;
                if (is_numeric($rawDate)) {
                    try {
                        $parsedDate = Date::excelToDateTimeObject($rawDate)->format('Y-m-d');
                    } catch (\Exception $e) {}
                } else {
                    $time = strtotime((string)$rawDate);
                    if ($time !== false) {
                        $parsedDate = date('Y-m-d', $time);
                    }
                }

                // Если это реально дата (а не слово "Дата"), запоминаем ее
                if ($parsedDate) {
                    $lastValidDate = $parsedDate;
                }
            }

            // 2. Ищем номер пары
            $pairNumber = null;
            if (!empty($rawPair)) {
                preg_match('/\d+/', (string)$rawPair, $matches);
                if (!empty($matches)) {
                    $pairNumber = $matches[0];
                }
            }

            // 3. Если есть и дата (текущая или предыдущая объединенная), и пара — сохраняем колонку
            if ($lastValidDate && $pairNumber) {
                $columnMap[$i] = $lastValidDate . '-' . $pairNumber;
            }
        }

        // ПЕРЕБОР СТУДЕНТОВ
        $dataRows = $rows->slice(2); // Пропускаем 2 строки заголовков

        foreach ($dataRows as $row) {
            $excelName = trim($row[0] ?? '');
            if (empty($excelName)) continue;

            // Ищем студента по частичному совпадению ФИО
            $student = null;
            $excelNameLower = mb_strtolower($excelName);

            foreach ($validStudents as $s) {
                $dbName = mb_strtolower(trim($s->last_name . ' ' . $s->first_name));
                // Если ФИО из базы содержится в ФИО из Excel (например, "Алиев Эмиль" в "Алиев Эмиль Маратович")
                if (mb_strpos($excelNameLower, $dbName) !== false) {
                    $student = $s;
                    break;
                }
            }

            if (!$student) {
                if (!in_array($excelName, $this->skippedStudents)) {
                    $this->skippedStudents[] = $excelName;
                }
                continue; 
            }

            // ПЕРЕБОР ОЦЕНОК
            foreach ($columnMap as $colIndex => $scheduleKey) {
                $rawMarkName = trim($row[$colIndex] ?? '');
                if (empty($rawMarkName) || $rawMarkName === '—') continue;

                $markName = mb_strtolower($rawMarkName);

                if (!$validSchedules->has($scheduleKey)) {
                    if (!in_array($scheduleKey, $this->skippedDates)) {
                        $this->skippedDates[] = $scheduleKey;
                    }
                    continue; 
                }
                $schedule = $validSchedules[$scheduleKey];

                if (!$marks->has($markName)) {
                    if (!in_array($rawMarkName, $this->skippedMarks)) {
                        $this->skippedMarks[] = $rawMarkName;
                    }
                    continue; 
                }
                $mark = $marks[$markName];

                // Сохраняем
                Attendance::updateOrCreate(
                    ['student_id' => $student->id, 'schedule_id' => $schedule->id],
                    ['mark_id' => $mark->id]
                );
                $this->savedCount++;
            }
        }
    }
}