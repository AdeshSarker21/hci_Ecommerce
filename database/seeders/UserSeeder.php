<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@ecommerce.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->roles()->syncWithoutDetaching(
            \App\Models\Role::where('slug', 'super-admin')->pluck('id')
        );

        $admin = User::updateOrCreate(
            ['email' => 'admin@ecommerce.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->roles()->syncWithoutDetaching(
            \App\Models\Role::where('slug', 'admin')->pluck('id')
        );

        $customer = User::updateOrCreate(
            ['email' => 'customer@ecommerce.com'],
            [
                'name' => 'Test Customer',
                'password' => Hash::make('password'),
                'status' => 'active',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $customer->roles()->syncWithoutDetaching(
            \App\Models\Role::where('slug', 'customer')->pluck('id')
        );
    }
}
