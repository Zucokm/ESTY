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
        // Create Admin account
        User::factory()->create([
            'name' => 'ESTY Admin',
            'email' => 'admin@shop.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create standard test Customer account
        User::factory()->create([
            'name' => 'John Customer',
            'email' => 'customer@shop.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        // Create 5 random Customer accounts
        User::factory(5)->create([
            'role' => 'customer',
        ]);

        $this->call([
            ProductSeeder::class,
            HomepageSeeder::class,
        ]);
    }
}
