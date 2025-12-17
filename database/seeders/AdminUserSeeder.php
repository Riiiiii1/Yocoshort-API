<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'ZomerAdmin',
            'email' => 'admin@yocoshort.com', 
            'password' => Hash::make('yq1jbM7z'), 
            'role' => 'admin', 
            'email_verified_at' => now(),
        ]);
    }
}
