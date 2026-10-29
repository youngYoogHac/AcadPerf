<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SubjectSeeder::class,
            LessonTypeSeeder::class,
            MarkSeeder::class,
            UserSeeder::class,
            GroupSeeder::class,
            StudentSeeder::class,
            ScheduleSeeder::class,
        ]);
    }
}