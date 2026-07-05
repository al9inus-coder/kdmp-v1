<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'superadmin@kdmp.local'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('123PassKDMP!@#'),
                'is_active' => true,
            ]
        )->assignRole('Super Admin');

        User::firstOrCreate(
            ['email' => 'admin@kdmp.local'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        )->assignRole('Admin');

        User::firstOrCreate(
            ['email' => 'kabid@kdmp.local'],
            [
                'name' => 'Kabid',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        )->assignRole('Kabid');

        User::firstOrCreate(
            ['email' => 'staff@kdmp.local'],
            [
                'name' => 'Staff',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        )->assignRole('Staff');
    }
}