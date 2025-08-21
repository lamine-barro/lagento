<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DiagnosticController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\IntelligenceController;
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

// Pages SEO stratégiques
Route::get('/assistant-ia-entrepreneur-cote-ivoire', function () {
    return view('seo.assistant-ia-entrepreneur');
})->name('seo.assistant-ia');

Route::get('/financement-startup-pme-abidjan', function () {
    return view('seo.financement-startup');
})->name('seo.financement');

Route::get('/diagnostic-entreprise-gratuit-ci', function () {
    return view('seo.diagnostic-entreprise');
})->name('seo.diagnostic');

Route::get('/conseil-business-innovation-afrique', function () {
    return view('seo.conseil-business');
})->name('seo.conseil');

Route::get('/opportunites-entrepreneur-cote-ivoire', function () {
    return view('seo.opportunites');
})->name('seo.opportunites');

Route::get('/reseau-entrepreneurs-ivoiriens', function () {
    return view('seo.reseau-entrepreneurs');
})->name('seo.reseau');

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
    // Onboarding (pas de vérification d'onboarding pour ces routes)
    Route::prefix('onboarding')->group(function () {
        Route::get('/step1', [OnboardingController::class, 'showStep1'])->name('onboarding.step1');
        Route::post('/step1', [OnboardingController::class, 'processStep1'])->name('onboarding.step1.process');
        Route::get('/step2', [OnboardingController::class, 'showStep2'])->name('onboarding.step2');
        Route::post('/step2', [OnboardingController::class, 'processStep2'])->name('onboarding.step2.process');
        Route::get('/step3', [OnboardingController::class, 'showStep3'])->name('onboarding.step3');
        Route::post('/step3', [OnboardingController::class, 'processStep3'])->name('onboarding.step3.process');
        Route::get('/step4', [OnboardingController::class, 'showStep4'])->name('onboarding.step4');
        Route::post('/step4', [OnboardingController::class, 'processStep4'])->name('onboarding.step4.process');
    });
    
    // Route intelligence spéciale (pas de restriction d'onboarding)
    Route::get('/intelligence', [IntelligenceController::class, 'index'])->name('intelligence');
    Route::get('/intelligence/projects', [IntelligenceController::class, 'projects'])->name('intelligence.projects');

    // Routes protégées qui nécessitent un onboarding complet
    Route::middleware(['onboarding.complete'])->group(function () {
        Route::get('/diagnostic', [DiagnosticController::class, 'index'])->name('diagnostic');
        Route::post('/diagnostic/run', [DiagnosticController::class, 'runDiagnostic'])->name('diagnostic.run');
        Route::get('/profile', [DiagnosticController::class, 'profile'])->name('profile');
        Route::post('/profile', [DiagnosticController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/project', [DiagnosticController::class, 'updateProject'])->name('profile.project.update');
        Route::delete('/profile', [DiagnosticController::class, 'deleteProfile'])->name('profile.delete');
        
        // Chat
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::post('/chat/save-user-message', [ChatController::class, 'saveUserMessage'])->name('chat.save-user-message');
        Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
        Route::get('/chat/suggestions', [ChatController::class, 'getSuggestions'])->name('chat.suggestions');
        Route::post('/chat/suggestions/refresh', [ChatController::class, 'refreshSuggestions'])->name('chat.suggestions.refresh');
        Route::post('/chat/conversations/create', [ChatController::class, 'createConversation'])->name('chat.conversations.create');
        Route::get('/chat/conversations', [ChatController::class, 'getConversations'])->name('chat.conversations.get');
        Route::get('/chat/conversations/{conversationId}/messages', [ChatController::class, 'getConversationMessages'])->name('chat.conversations.messages');
        Route::delete('/chat/conversations/{conversationId}', [ChatController::class, 'deleteConversation'])->name('chat.conversations.delete');
        
        // Test streaming endpoint
        Route::get('/test-stream', [ChatController::class, 'testStream'])->name('test.stream');
        
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
            Route::get('/view/{filename}', [DocumentController::class, 'view'])->name('documents.view')->where('filename', '.*');
            Route::get('/download/{filename}', [DocumentController::class, 'download'])->name('documents.download')->where('filename', '.*');
            Route::delete('/delete/{filename}', [DocumentController::class, 'delete'])->name('documents.delete')->where('filename', '.*');
        });
        
        // Email change with OTP verification
        Route::post('/email-change/send-otp', [DiagnosticController::class, 'sendEmailChangeOtp'])->name('email-change.send-otp');
        Route::post('/email-change/verify', [DiagnosticController::class, 'verifyEmailChange'])->name('email-change.verify');
    });
    
    // Projets (annuaire public accessible sans onboarding complet)
    Route::prefix('projets')->group(function () {
        Route::get('/', [ProjetController::class, 'index'])->name('projets.index');
        Route::get('/{projet}', [ProjetController::class, 'show'])->name('projets.show');
        Route::post('/{projet}/toggle-visibility', [ProjetController::class, 'toggleVisibility'])->name('projets.toggle-visibility');
        Route::get('/api/search', [ProjetController::class, 'search'])->name('projets.search');
    });
});
