<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [AuthController::class, 'showEmailForm'])->name('landing');
Route::get('/login', [AuthController::class, 'showEmailForm'])->name('login');

Route::get('/legal', function () {
    return view('legal');
})->name('legal');

// SEO routes
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemap-projets.xml', [SitemapController::class, 'projets'])->name('sitemap.projets');

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/email', [AuthController::class, 'sendOtp'])->name('auth.email');
    Route::get('/verify-otp', [AuthController::class, 'showOtpForm'])->name('auth.verify-otp-form');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('auth.verify-otp');
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('auth.resend-otp');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Onboarding
    Route::prefix('onboarding')->group(function () {
        Route::get('/step1', [OnboardingController::class, 'showStep1'])->name('onboarding.step1');
        Route::post('/step1', [OnboardingController::class, 'processStep1']);
        Route::get('/step2', [OnboardingController::class, 'showStep2'])->name('onboarding.step2');
        Route::post('/step2', [OnboardingController::class, 'processStep2']);
        Route::get('/step3', [OnboardingController::class, 'showStep3'])->name('onboarding.step3');
        Route::post('/step3', [OnboardingController::class, 'processStep3']);
        Route::get('/step4', [OnboardingController::class, 'showStep4'])->name('onboarding.step4');
        Route::post('/step4', [OnboardingController::class, 'processStep4']);
    });
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::post('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/project', [DashboardController::class, 'updateProject'])->name('profile.project.update');
    
    // Chat
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/suggestions', [ChatController::class, 'getSuggestions'])->name('chat.suggestions');
    Route::post('/chat/suggestions/refresh', [ChatController::class, 'refreshSuggestions'])->name('chat.suggestions.refresh');
    
    // Conversations
    Route::get('/conversations', function () {
        $conversations = \App\Models\UserConversation::where('user_id', auth()->id())
            ->with('lastMessage')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('last_message_at', 'desc')
            ->get();
        return view('conversations.index', compact('conversations'));
    })->name('conversations.index');
    
    // Documents
    Route::prefix('documents')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('/upload', [DocumentController::class, 'upload'])->name('documents.upload');
        Route::get('/download/{filename}', [DocumentController::class, 'download'])->name('documents.download');
    });
    
    // Projets
    Route::prefix('projets')->group(function () {
        Route::get('/', [ProjetController::class, 'index'])->name('projets.index');
        Route::get('/mes-projets', [ProjetController::class, 'mesProjects'])->name('projets.mes-projets');
        Route::get('/create', [ProjetController::class, 'create'])->name('projets.create');
        Route::post('/', [ProjetController::class, 'store'])->name('projets.store');
        Route::get('/{projet}', [ProjetController::class, 'show'])->name('projets.show');
        Route::get('/{projet}/edit', [ProjetController::class, 'edit'])->name('projets.edit');
        Route::put('/{projet}', [ProjetController::class, 'update'])->name('projets.update');
        Route::delete('/{projet}', [ProjetController::class, 'destroy'])->name('projets.destroy');
        Route::post('/{projet}/toggle-visibility', [ProjetController::class, 'toggleVisibility'])->name('projets.toggle-visibility');
        Route::get('/api/search', [ProjetController::class, 'search'])->name('projets.search');
    });
});
