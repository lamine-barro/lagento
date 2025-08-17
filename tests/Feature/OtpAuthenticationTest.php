<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OtpAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test OTP email sending process
     */
    public function test_otp_email_can_be_sent(): void
    {
        Mail::fake();

        $response = $this->post('/auth/email', [
            'email' => 'test@example.com'
        ]);

        $response->assertRedirect(route('auth.verify-otp-form'));
        $response->assertSessionHas('success', 'Un code de vérification a été envoyé à votre email.');
        
        // Check session data
        $this->assertNotNull(session('email'));
        $this->assertNotNull(session('otp'));
        $this->assertNotNull(session('otp_expires_at'));
        
        // Check email was sent
        Mail::assertSent(\App\Mail\OtpMail::class);
    }

    /**
     * Test OTP verification process
     */
    public function test_otp_verification_works(): void
    {
        // Simulate OTP in session
        session([
            'email' => 'test@example.com',
            'otp' => '123456',
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        $response = $this->post('/auth/verify-otp', [
            'otp' => '123456'
        ]);

        $response->assertRedirect();
        $this->assertAuthenticated();
        
        // Check session is cleared
        $this->assertNull(session('otp'));
    }

    /**
     * Test invalid OTP rejection
     */
    public function test_invalid_otp_is_rejected(): void
    {
        session([
            'email' => 'test@example.com',
            'otp' => '123456',
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        $response = $this->post('/auth/verify-otp', [
            'otp' => '999999'
        ]);

        $response->assertSessionHasErrors(['otp' => 'Code OTP incorrect.']);
        $this->assertGuest();
    }

    /**
     * Test expired OTP rejection
     */
    public function test_expired_otp_is_rejected(): void
    {
        session([
            'email' => 'test@example.com',
            'otp' => '123456',
            'otp_expires_at' => now()->subMinutes(1) // Expired
        ]);

        $response = $this->post('/auth/verify-otp', [
            'otp' => '123456'
        ]);

        $response->assertSessionHasErrors(['otp' => 'Code OTP expiré. Veuillez en demander un nouveau.']);
        $this->assertGuest();
    }

    /**
     * Test OTP resend functionality
     */
    public function test_otp_can_be_resent(): void
    {
        Mail::fake();
        
        session(['email' => 'test@example.com']);

        $response = $this->post('/auth/resend-otp');

        $response->assertSessionHas('success', 'Un nouveau code de vérification a été envoyé à votre email.');
        
        // Check new OTP is in session
        $this->assertNotNull(session('otp'));
        $this->assertNotNull(session('otp_expires_at'));
        
        // Check email was sent
        Mail::assertSent(\App\Mail\OtpMail::class);
    }
}
