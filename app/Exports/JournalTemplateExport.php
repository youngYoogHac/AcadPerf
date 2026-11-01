<?php

namespace App\Exports;

use App\Models\Group;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use Maatwebsite\Excel\Concerns\FromArray;
use Carbon\Carbon;

class JournalTemplateExport implements FromArray
{
    protected $group;
    protected $subject;

    public function __construct(Group $group, Subject $subject)
    {
        $this->group = $group;
        $this->subject = $subject;
    }

    public function array(): array
    {
        $students = Student::where('group_id', $this->group->id)->orderBy('last_name')->get();
        $schedules = Schedule::where('group_id', $this->group->id)
            ->where('subject_id', $this->subject->id)
            ->orderBy('date')->get();

        $data = [];

        // Строка 1: Заголовки (ФИО, подгруппа, Дата, сами даты)
        $row1 = ['ФИО', 'под группа', 'Дата'];
        $row2 = ['', '', 'пары'];

        foreach ($schedules as $schedule) {
            $row1[] = Carbon::parse($schedule->date)->format('d.m.Y');
            $row2[] = 'пара ' . $schedule->pair_number;
        }

        $data[] = $row1;
        $data[] = $row2;

        // Строки со студентами
        foreach ($students as $student) {
            $studentRow = [
                $student->last_name . ' ' . $student->first_name, // ФИО
                '1.0', // Подгруппа (заглушка)
                ''     // Дата (пустая колонка)
            ];
            
            // Заполняем пустые ячейки под даты
            foreach ($schedules as $schedule) {
                $studentRow[] = '';
            }
            $data[] = $studentRow;
        }

        return $data;
    }
}