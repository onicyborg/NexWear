<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin Default', 'email' => 'admin@nexwear.com', 'role' => UserRole::Admin],
            ['name' => 'User Cutting', 'email' => 'cutting@nexwear.com', 'role' => UserRole::Cutting],
            ['name' => 'User Sewing', 'email' => 'sewing@nexwear.com', 'role' => UserRole::Sewing],
            ['name' => 'User QC', 'email' => 'qc@nexwear.com', 'role' => UserRole::QC],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('Qwerty123*'),
                    'role' => $u['role'],
                ]
            );
        }
    }
}
