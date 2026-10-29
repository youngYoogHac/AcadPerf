<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'teacher', 'student'] as $name) {
            Role::updateOrCreate(['name' => $name]);
        }
    }
}