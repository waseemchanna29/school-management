<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::updateOrCreate(['email' => 'super@school.com'], [
            'name'     => 'Super Admin',
            'email'    => 'super@school.com',
            'password' => Hash::make('super@1234'),
            'role'     => 'super_admin',
        ]);

        // Demo Admin
        User::updateOrCreate(['email' => 'admin@school.com'], [
            'name'     => 'Campus Admin',
            'email'    => 'admin@school.com',
            'password' => Hash::make('admin@1234'),
            'role'     => 'admin',
        ]);
    }
}