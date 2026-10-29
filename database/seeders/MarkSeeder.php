<?php

namespace Database\Seeders;

use App\Models\Mark;
use Illuminate\Database\Seeder;

class MarkSeeder extends Seeder
{
    public function run(): void
    {
        $marks = [
            ['name' => '5', 'type' => 'grade'],
            ['name' => '4', 'type' => 'grade'],
            ['name' => '3', 'type' => 'grade'],
            ['name' => '2', 'type' => 'grade'],
            ['name' => 'Н', 'type' => 'attendance'],
            ['name' => 'Б', 'type' => 'attendance'],
        ];

        foreach ($marks as $mark) {
            Mark::updateOrCreate(['name' => $mark['name']], $mark);
        }
    }
}