<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@kbt.uz',
            'password' => Hash::make('Admin@2026!'),
        ]);

        $admin->assignRole('super-admin');
    }
}
