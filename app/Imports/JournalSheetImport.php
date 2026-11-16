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
    protected $parent;

    public function __construct($group, $subject, $parent)
    {
        $this->group = $group;
        $this->subject = $subject;
        $this->parent = $parent;
        $this->parent->sheetFound = true; 
    }

    public function collection(Collection $rows): void
    {
        // Сортируем студентов по длине ФИО, чтобы длинные имена искались первыми
        $validStudents = Student::where('group_id', $this->group->id)->get()
            ->sortByDesc(fn($s) => mb_strlen(trim($s->last_name . ' ' . $s->first_name . ' ' . $s->middle_name)))
            ->values();

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

        // СОРТИРОВКА КОЛОНОК ПО ДАТЕ И НОМЕРУ ПАРЫ
        asort($columnMap);

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

            // Обработка подгруппы (убираем лишние нули)
            $subgroup = isset($row[1]) ? trim((string)$row[1]) : null;
            if (is_numeric($subgroup) && strpos($subgroup, '.') !== false) {
                $subgroup = (string)(float)$subgroup; 
            }

            if (!$student) {
                $nameParts = preg_split('/\s+/', $excelName);
                $student = Student::create([
                    'group_id'    => $this->group->id,
                    'subgroup'    => $subgroup,
                    'last_name'   => $nameParts[0] ?? 'Неизвестно',
                    'first_name'  => $nameParts[1] ?? '',
                    'middle_name' => $nameParts[2] ?? '',
                ]);
                $validStudents->push($student);
            } else {
                if ($subgroup && $student->subgroup !== $subgroup) {
                    $student->update(['subgroup' => $subgroup]);
                }
            }

            foreach ($columnMap as $colIndex => $scheduleKey) {
                $rawMarkName = trim($row[$colIndex] ?? '');
                if (empty($rawMarkName) || $rawMarkName === '—') continue;

                $markName = mb_strtolower($rawMarkName);

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

                $mark = $marks->get($markName);

                if (!$mark) {
                    $type = (preg_match('/[0-9\/]/', $rawMarkName)) ? 'grade' : 'attendance';
                    $mark = Mark::firstOrCreate(
                        ['name' => $rawMarkName], 
                        ['type' => $type]
                    );
                    $marks->put($markName, $mark);
                }

                Attendance::updateOrCreate(
                    ['student_id' => $student->id, 'schedule_id' => $schedule->id],
                    ['mark_id' => $mark->id]
                );
                $this->parent->savedCount++;
            }
        }
    }
}