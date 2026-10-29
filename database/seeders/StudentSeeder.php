<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $group = Group::where('name', 'Пдо-31')->first();

        Student::updateOrCreate(
            ['last_name' => 'Петров', 'first_name' => 'Петр', 'group_id' => $group->id]
        );

        Student::updateOrCreate(
            ['last_name' => 'Сидоров', 'first_name' => 'Сидор', 'group_id' => $group->id]
        );
    }
}