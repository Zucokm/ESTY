<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create / Update Admin accounts
        User::updateOrCreate(
            ['email' => 'admin@shop.com'],
            [
                'name' => 'ESTY Admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'ESTY Admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        /*
        // --- Optional Demo Data (Commented out for Production) ---
        // User::factory()->create([
        //     'name' => 'John Customer',
        //     'email' => 'customer@shop.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'customer',
        // ]);
        // User::factory(5)->create([
        //     'role' => 'customer',
        // ]);
        // $this->call([
        //     ProductSeeder::class,
        //     HomepageSeeder::class,
        // ]);
        */
    }
}
