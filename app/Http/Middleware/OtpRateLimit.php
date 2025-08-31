<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\OtpRateLimit as OtpRateLimitModel;
use Illuminate\Support\Facades\Log;

class OtpRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 5): Response
    {
        $ipAddress = $request->ip();
        
        // Check if IP has exceeded daily limit
        if (OtpRateLimitModel::hasExceededLimit($ipAddress, $maxAttempts)) {
            $remaining = OtpRateLimitModel::getRemainingAttempts($ipAddress, $maxAttempts);
            
            Log::warning("OTP rate limit exceeded for IP: {$ipAddress}");
            
            return back()->withErrors([
                'email' => "Limite quotidienne d'authentifications atteinte ({$maxAttempts} tentatives). Réessayez demain."
            ])->withInput();
        }

        $response = $next($request);

        // If the request was successful (OTP was sent - check for redirect to OTP form), increment the counter
        if ($request->isMethod('POST') && $response->isRedirect() && str_contains($response->headers->get('location'), 'verify-otp')) {
            $email = $request->input('email');
            OtpRateLimitModel::incrementAttempts($ipAddress, $email);
            
            $remaining = OtpRateLimitModel::getRemainingAttempts($ipAddress, $maxAttempts);
            Log::info("OTP attempt recorded for IP: {$ipAddress}, email: {$email}, remaining: {$remaining}");
        }

        return $response;
    }
}
