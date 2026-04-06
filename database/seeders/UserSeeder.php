<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Ariel Zakly Pratama (IT Staff)
        User::create([
            'name' => 'Ariel Zakly Pratama',
            'email' => 'ariel@rs.com', // Silakan ganti emailnya
            'password' => Hash::make('password'), // Password default: password
            'is_it_staff' => true,
        ]);

        // Akun Yordani Laode (IT Staff)
        User::create([
            'name' => 'Yordani Laode',
            'email' => 'yordani@rs.com', // Silakan ganti emailnya
            'password' => Hash::make('password'),
            'is_it_staff' => true,
        ]);
    }
}