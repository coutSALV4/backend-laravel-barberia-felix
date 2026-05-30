<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@barberia.com'],
            [
                'name'                 => 'Admin',
                'lastname'             => 'Principal',
                'role'                 => 'admin',
                'password'             => User::DEFAULT_PASSWORD,
                'must_change_password' => true,
                'email_verified_at'    => now(),
            ]
        );
    }
}