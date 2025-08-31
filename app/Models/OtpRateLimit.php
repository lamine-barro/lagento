<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OtpRateLimit extends Model
{
    protected $fillable = [
        'ip_address',
        'email',
        'attempts',
        'date'
    ];

    protected $casts = [
        'date' => 'date',
        'attempts' => 'integer'
    ];

    /**
     * Check if IP has exceeded daily OTP limit
     */
    public static function hasExceededLimit(string $ipAddress, int $maxAttempts = 5): bool
    {
        $today = Carbon::today();
        
        $record = self::where('ip_address', $ipAddress)
                     ->where('date', $today)
                     ->first();
        
        return $record && $record->attempts >= $maxAttempts;
    }

    /**
     * Increment attempts for IP address
     */
    public static function incrementAttempts(string $ipAddress, ?string $email = null): void
    {
        $today = Carbon::today();
        
        $record = self::where('ip_address', $ipAddress)
                     ->where('date', $today)
                     ->first();

        if ($record) {
            $record->increment('attempts');
            if ($email && !$record->email) {
                $record->update(['email' => $email]);
            }
        } else {
            self::create([
                'ip_address' => $ipAddress,
                'date' => $today,
                'email' => $email,
                'attempts' => 1
            ]);
        }
    }

    /**
     * Get remaining attempts for IP address
     */
    public static function getRemainingAttempts(string $ipAddress, int $maxAttempts = 5): int
    {
        $today = Carbon::today();
        
        $record = self::where('ip_address', $ipAddress)
                     ->where('date', $today)
                     ->first();
        
        if (!$record) {
            return $maxAttempts;
        }
        
        return max(0, $maxAttempts - $record->attempts);
    }

    /**
     * Clean old records (keep only last 30 days)
     */
    public static function cleanOldRecords(): int
    {
        return self::where('date', '<', Carbon::today()->subDays(30))->delete();
    }
}
