<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\LessonType;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::where('login', 'teacher1')->first();
        $group = Group::where('name', 'Пдо-31')->first();
        $subject = Subject::where('name', 'Системное программирование')->first();
        $lessonType = LessonType::where('name', 'Практика')->first();

        $dates = [
        '2026-09-04',
        '2026-09-07',
        '2026-09-08',
        '2026-09-09',
        '2026-09-10',
        '2026-09-15',
        ];

        foreach ($dates as $date) {
            Schedule::updateOrCreate(
                [
                    'group_id' => $group->id, 
                    'date' => $date, 
                    'pair_number' => 1
                ],
                [
                    'user_id' => $teacher->id, 
                    'subject_id' => $subject->id, 
                    'lesson_type_id' => $lessonType->id
                ]
            );
        }
    }
}