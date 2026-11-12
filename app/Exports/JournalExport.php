<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class JournalExport implements FromArray, WithTitle
{
    protected $group;
    protected $subject;
    protected $students;
    protected $schedules;
    protected $attendances;

    public function __construct($group, $subject, $students, $schedules, $attendances)
    {
        $this->group = $group;
        $this->subject = $subject;
        $this->students = $students;
        $this->schedules = $schedules;
        $this->attendances = $attendances;
    }

    public function title(): string
    {
        return $this->group->name;
    }

    public function array(): array
    {
        $data = [];
        $row1 = ['ФИО', 'под группа', 'Дата'];
        $row2 = ['', '', 'пары'];

        foreach ($this->schedules as $schedule) {
            $row1[] = Carbon::parse($schedule->date)->format('d.m.Y');
            $row2[] = 'пара ' . $schedule->pair_number;
        }

        $data[] = $row1;
        $data[] = $row2;

        foreach ($this->students as $student) {
            $fullName = trim($student->last_name . ' ' . $student->first_name . ' ' . $student->middle_name);
            
            $studentRow = [$fullName, $student->subgroup ?? '', ''];
            
            foreach ($this->schedules as $schedule) {
                $mark = $this->attendances[$student->id][$schedule->id]->mark->name ?? ''; 
                $studentRow[] = $mark;
            }
            $data[] = $studentRow;
        }

        return $data;
    }
}