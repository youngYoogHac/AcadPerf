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
            ['last_name' => 'Алиев', 'first_name' => 'Эмиль', 'group_id' => $group->id]
        );

        Student::updateOrCreate(
            ['last_name' => 'Аллахвердиев', 'first_name' => 'Эмин', 'group_id' => $group->id]
        );

        Student::updateOrCreate(
            ['last_name' => 'Ларичев', 'first_name' => 'Сергей', 'group_id' => $group->id]
        );

        Student::updateOrCreate(
            ['last_name' => 'Емельянов', 'first_name' => 'Михаил', 'group_id' => $group->id]
        );
    }
}