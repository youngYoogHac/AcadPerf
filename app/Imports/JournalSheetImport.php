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

class JournalSheetImport implements ToCollection
{
    protected $group;
    protected $subject;
    protected $parent; // Ссылка на главный класс

    public function __construct($group, $subject, $parent)
    {
        $this->group = $group;
        $this->subject = $subject;
        $this->parent = $parent;
        // Если этот класс был вызван, значит нужный лист найден!
        $this->parent->sheetFound = true; 
    }

    public function collection(Collection $rows): void
    {
        $validStudents = Student::where('group_id', $this->group->id)->get();

        $validSchedules = Schedule::where('group_id', $this->group->id)
            ->where('subject_id', $this->subject->id)
            ->get()
            ->keyBy(fn($schedule) => Carbon::parse($schedule->date)->format('Y-m-d') . '-' . $schedule->pair_number);

        $marks = Mark::all()->keyBy(fn($mark) => mb_strtolower(trim($mark->name)));

        $dateRow = $rows[0]; 
        $pairRow = $rows[1]; 

        $columnMap = []; 
        $lastValidDate = null; 
        
        for ($i = 0; $i < count($dateRow); $i++) {
            $rawDate = $dateRow[$i] ?? null;
            $rawPair = $pairRow[$i] ?? null;

            if (!empty($rawDate)) {
                $parsedDate = false;
                if (is_numeric($rawDate)) {
                    try { $parsedDate = Date::excelToDateTimeObject($rawDate)->format('Y-m-d'); } catch (\Exception $e) {}
                } else {
                    $time = strtotime((string)$rawDate);
                    if ($time !== false) $parsedDate = date('Y-m-d', $time);
                }
                if ($parsedDate) $lastValidDate = $parsedDate;
            }

            $pairNumber = null;
            if (!empty($rawPair)) {
                preg_match('/\d+/', (string)$rawPair, $matches);
                if (!empty($matches)) $pairNumber = $matches[0];
            }

            if ($lastValidDate && $pairNumber) {
                $columnMap[$i] = $lastValidDate . '-' . $pairNumber;
            }
        }

        $dataRows = $rows->slice(2); 

        foreach ($dataRows as $row) {
            $excelName = trim($row[0] ?? '');
            if (empty($excelName)) continue;

            $student = null;
            $excelNameLower = mb_strtolower($excelName);

            foreach ($validStudents as $s) {
                $dbName = mb_strtolower(trim($s->last_name . ' ' . $s->first_name));
                if (mb_strpos($excelNameLower, $dbName) !== false) {
                    $student = $s;
                    break;
                }
            }

            // Пытаемся считать подгруппу из колонки B (индекс 1)
            $subgroup = isset($row[1]) ? trim($row[1]) : null;

            // АВТОСОЗДАНИЕ СТУДЕНТА
            if (!$student) {
                $nameParts = explode(' ', trim($excelName));
                $student = Student::create([
                    'group_id'    => $this->group->id,
                    'subgroup'    => $subgroup,
                    'last_name'   => $nameParts[0] ?? 'Неизвестно',
                    'first_name'  => $nameParts[1] ?? '',
                    'middle_name' => $nameParts[2] ?? '',
                ]);
                $validStudents->push($student);
            } else {
                // Если студент найден, но подгруппа изменилась — обновляем её
                if ($subgroup && $student->subgroup !== $subgroup) {
                    $student->update(['subgroup' => $subgroup]);
                }
            }

            foreach ($columnMap as $colIndex => $scheduleKey) {
                $rawMarkName = trim($row[$colIndex] ?? '');
                if (empty($rawMarkName) || $rawMarkName === '—') continue;

                $markName = mb_strtolower($rawMarkName);

                // АВТОСОЗДАНИЕ ПАРЫ
                if (!$validSchedules->has($scheduleKey)) {
                    $parts = explode('-', $scheduleKey);
                    $pairNumber = array_pop($parts);
                    $date = implode('-', $parts);

                    $schedule = Schedule::firstOrCreate(
                        [
                            'group_id'    => $this->group->id,
                            'subject_id'  => $this->subject->id,
                            'date'        => $date,
                            'pair_number' => $pairNumber,
                        ],
                        [
                            'user_id'        => auth()->id() ?? 1,
                            'lesson_type_id' => \App\Models\LessonType::first()->id ?? 1,
                        ]
                    );
                    $validSchedules->put($scheduleKey, $schedule);
                }
                $schedule = $validSchedules[$scheduleKey];

                // Ищем оценку в загруженном справочнике
                $mark = $marks->get($markName);

                // ЕСЛИ ОЦЕНКИ НЕТ — СОЗДАЕМ ЕЕ АВТОМАТИЧЕСКИ (как в ручном режиме)
                if (!$mark) {
                    // Определяем тип: если есть цифры или слэш, это оценка, иначе посещаемость
                    $type = (preg_match('/[0-9\/]/', $rawMarkName)) ? 'grade' : 'attendance';
                    
                    $mark = Mark::firstOrCreate(
                        ['name' => $rawMarkName], // Оригинальное название (например, "3 // 4")
                        ['type' => $type]
                    );
                    
                    // Добавляем в память, чтобы для следующих студентов не делать запрос к БД
                    $marks->put($markName, $mark);
                }

                Attendance::updateOrCreate(
                    ['student_id' => $student->id, 'schedule_id' => $schedule->id],
                    ['mark_id' => $mark->id]
                );
                // Увеличиваем счетчик в ГЛАВНОМ классе
                $this->parent->savedCount++;
            }
        }
    }
}