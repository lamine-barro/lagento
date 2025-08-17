<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showEmailForm()
    {
        return view('landing');
    }
    
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        
        $email = $request->email;
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP in session
        session([
            'email' => $email,
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10)
        ]);
        
        // Get user name if exists
        $user = User::where('email', $email)->first();
        $userName = $user ? $user->name : 'Entrepreneur';
        
        try {
            // Send OTP via email
            Mail::to($email)->send(new OtpMail($otp, $userName));
            Log::info("OTP sent to {$email}: {$otp}");
        } catch (\Exception $e) {
            Log::error("Failed to send OTP email to {$email}: " . $e->getMessage());
            return back()->withErrors(['email' => 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer.']);
        }
        
        return redirect()->route('auth.verify-otp-form')->with('success', 'Un code de vérification a été envoyé à votre email.');
    }
    
    public function showOtpForm()
    {
        if (!session('email')) {
            return redirect()->route('landing');
        }
        
        return view('auth.verify-otp');
    }
    
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6'
        ]);
        
        $sessionOtp = session('otp');
        $otpExpiresAt = session('otp_expires_at');
        
        if (!$sessionOtp || !$otpExpiresAt) {
            return back()->withErrors(['otp' => 'Session expirée. Veuillez recommencer.']);
        }
        
        if (now()->gt($otpExpiresAt)) {
            return back()->withErrors(['otp' => 'Code OTP expiré. Veuillez en demander un nouveau.']);
        }
        
        if ($request->otp !== $sessionOtp) {
            return back()->withErrors(['otp' => 'Code OTP incorrect.']);
        }
        
        // Find or create user
        $user = User::firstOrCreate(
            ['email' => session('email')],
            [
                'name' => explode('@', session('email'))[0],
                'email_verified_at' => now()
            ]
        );
        
        // Ensure email is verified
        if (!$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }
        
        // Login user
        Auth::login($user, true);
        
        // Clear OTP session
        session()->forget(['email', 'otp', 'otp_expires_at']);
        
        // Check if user needs onboarding
        if (!$user->company_name || !$user->business_sector) {
            return redirect()->route('onboarding.step1');
        }
        
        return redirect()->route('dashboard');
    }
    
    public function resendOtp()
    {
        $email = session('email');
        
        if (!$email) {
            return redirect()->route('landing');
        }
        
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        session([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10)
        ]);
        
        // Get user name if exists
        $user = User::where('email', $email)->first();
        $userName = $user ? $user->name : 'Entrepreneur';
        
        try {
            // Send new OTP via email
            Mail::to($email)->send(new OtpMail($otp, $userName));
            Log::info("New OTP sent to {$email}: {$otp}");
            
            return back()->with('success', 'Un nouveau code de vérification a été envoyé à votre email.');
        } catch (\Exception $e) {
            Log::error("Failed to resend OTP email to {$email}: " . $e->getMessage());
            return back()->withErrors(['email' => 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer.']);
        }
    }
    
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        
        return redirect()->route('landing');
    }
}