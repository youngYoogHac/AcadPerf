<?php

namespace Database\Seeders;

use App\Models\LessonType;
use Illuminate\Database\Seeder;

class LessonTypeSeeder extends Seeder
{
    public function run(): void
    {
        LessonType::updateOrCreate(['name' => 'Лекция']);
        LessonType::updateOrCreate(['name' => 'Практика']);
    }
}