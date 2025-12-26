<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => '$2y$12$pmtb97e/Bax8dYhFmNctc.hWAqsINFno1ZryUqrr060yuJCxzYtVm', // password
            'remember_token' => true,
        ]);
    }
}
