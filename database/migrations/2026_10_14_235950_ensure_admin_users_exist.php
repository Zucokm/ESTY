<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Admin 1: admin@shop.com (Password: admin123)
        User::updateOrCreate(
            ['email' => 'admin@shop.com'],
            [
                'name' => 'ESTY Admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Admin 2: admin@gmail.com (Password: admin123)
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'ESTY Admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
