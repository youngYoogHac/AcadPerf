<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        Group::updateOrCreate(['name' => 'Пдо-31']);
        Group::updateOrCreate(['name' => 'Пдо-32']);
    }
}