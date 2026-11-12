<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $teacherRole = Role::where('name', 'teacher')->first();

        User::updateOrCreate(
            ['login' => 'teacher'],
            ['name' => 'Преподаватель', 'password' => '123456', 'role_id' => $teacherRole->id]
        );
    }
}