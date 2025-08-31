<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OtpRateLimit;

class CleanOtpRateLimits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:clean-rate-limits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old OTP rate limit records (older than 30 days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning old OTP rate limit records...');
        
        $deletedCount = OtpRateLimit::cleanOldRecords();
        
        $this->info("Deleted {$deletedCount} old OTP rate limit records.");
        
        return 0;
    }
}
