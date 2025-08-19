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
            ['email' => 'test@horizon-o.ci'],
            [
                'name' => 'Entrepreneur Test',
                'email' => 'test@horizon-o.ci',
                'email_verified_at' => now(),
                'phone' => '+225 07 12 34 56 78',
                'profile_type' => 'entrepreneur',
                'verification_status' => 'verified'
            ]
        );

        $this->command->info('Test user created: test@horizon-o.ci (sans mot de passe - connexion par OTP uniquement)');
    }
}
