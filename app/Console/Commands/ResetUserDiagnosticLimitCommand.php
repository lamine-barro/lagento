<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetUserDiagnosticLimitCommand extends Command
{
    protected $signature = 'user:reset-diagnostic-limit {user_id? : The user ID (optional, will prompt if not provided)}';
    protected $description = 'Reset diagnostic usage limit for a specific user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if (!$userId) {
            $userId = $this->ask('Enter the user ID');
        }

        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User with ID '{$userId}' not found.");
            return 1;
        }

        $currentUsage = $user->diagnostics_used_this_week;
        
        $user->update([
            'diagnostics_used_this_week' => 0
        ]);

        $this->info("Diagnostic usage reset for user: {$user->name} ({$user->email})");
        $this->info("Previous usage: {$currentUsage}");
        $this->info("New usage: 0");
        $this->info("Remaining diagnostics this week: {$user->getRemainingUsage('diagnostics')}");

        return 0;
    }
}