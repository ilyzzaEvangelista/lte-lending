<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Admin::query()->firstOrCreate(
            ['username' => 'admin'],
            ['password' => 'password'],
        );

        User::query()->firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Demo Client',
                'first_name' => 'Demo',
                'last_name' => 'Client',
                'age' => 35,
                'gender' => 'other',
                'username' => 'democlient',
                'password' => 'password',
                'address' => '123 Main St',
                'contact' => '5550100',
                'status' => 1,
                'role' => User::ROLE_CLIENT,
            ],
        );
    }
}
