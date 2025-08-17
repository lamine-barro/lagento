<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'test@lagento.com'],
            [
                'name' => 'Entrepreneur Test',
                'email' => 'test@lagento.com',
                'email_verified_at' => now(),
                'phone' => '+225 07 12 34 56 78',
                'profile_type' => 'entrepreneur',
                'verification_status' => 'verified'
            ]
        );

        $this->command->info('Test user created: test@lagento.com (sans mot de passe - connexion par OTP uniquement)');
    }
}
