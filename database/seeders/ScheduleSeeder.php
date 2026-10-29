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
        $subject = Subject::where('name', 'Математика')->first();
        $lessonType = LessonType::where('name', 'Лекция')->first();

        Schedule::updateOrCreate(
            [
                'group_id' => $group->id, 
                'date' => now()->toDateString(), 
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