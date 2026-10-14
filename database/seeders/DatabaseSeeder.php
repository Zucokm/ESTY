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
        // 1. Create Admin account
        User::firstOrCreate(
            ['email' => 'admin@shop.com'],
            [
                'name' => 'ESTY Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
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
