<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $teacherRole = Role::where('name', 'teacher')->first();

        User::updateOrCreate(
            ['login' => 'admin'],
            ['name' => 'Завуч', 'password' => 'admin123', 'role_id' => $adminRole->id]
        );

        User::updateOrCreate(
            ['login' => 'teacher1'],
            ['name' => 'Иванов Иван', 'password' => 'teacher123', 'role_id' => $teacherRole->id]
        );
    }
}