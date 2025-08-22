<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use App\Models\UserAnalytics;
use App\Services\UserAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DiagnosticController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        try {
            // Get matched opportunities - data now comes from vectorized context
            $opportunities = collect(); // Empty collection - opportunities are handled via vector search
            
            // Récupérer les projets récents
            $recentProjects = Projet::where('user_id', $user->id)
                ->latest()
                ->limit(3)
                ->get();
            
            // Get user analytics
            $analytics = UserAnalytics::where('user_id', $user->id)->first();
            
            // Conversations récentes
            $recentConversations = \App\Models\UserConversation::where('user_id', $user->id)
                ->latest()
                ->limit(3)
                ->get();
            
            return view('diagnostic', compact(
                'opportunities', 
                'recentProjects', 
                'analytics',
                'recentConversations'
            ));
            
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une vue avec des données vides
            return view('diagnostic', [
                'opportunities' => collect(),
                'recentProjects' => collect(),
                'analytics' => null,
                'recentConversations' => collect()
            ]);
        }
    }
    
    public function profile()
    {
        $user = Auth::user();
        $project = Projet::where('user_id', $user->id)->first();
        return view('profile', compact('user', 'project'));
    }
    
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:30',
            'is_public' => 'boolean',
            'email_notifications' => 'boolean',
        ]);

        $user = Auth::user();
        $emailChanged = $user->email !== $request->email;
        
        // Si l'email a changé, réinitialiser la vérification
        if ($emailChanged) {
            // NE PAS mettre à jour l'email immédiatement, attendre la validation OTP
            // Mettre à jour seulement les autres champs
            $user->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'is_public' => $request->boolean('is_public'),
                'email_notifications' => $request->boolean('email_notifications'),
            ]);
            
            // Stocker le nouveau email temporairement et envoyer l'OTP
            session([
                'email_change_new_email' => $request->email,
                'email_change_otp' => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
                'email_change_otp_expires_at' => now()->addMinutes(10),
                'email_change_user_id' => $user->id
            ]);
            
            // Envoyer l'OTP à la nouvelle adresse
            try {
                Mail::to($request->email)->send(new \App\Mail\OtpMail(
                    session('email_change_otp'), 
                    $user->name,
                    'Validation de votre nouvelle adresse email'
                ));
                Log::info("Email change OTP sent to {$request->email} for user {$user->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send email change OTP to {$request->email}: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de l\'email de vérification. Veuillez réessayer.'
                ], 500);
            }
            
            return response()->json([
                'success' => true, 
                'email_change_pending' => true,
                'new_email' => $request->email,
                'message' => 'Profil mis à jour. Un code de vérification a été envoyé à votre nouvelle adresse email pour confirmer le changement.'
            ]);
        }
        
        $user->update($request->only([
            'name', 
            'phone',
            'is_public',
            'email_notifications'
        ]));

        return response()->json(['success' => true, 'message' => 'Profil mis à jour avec succès.']);
    }

    public function updateProject(Request $request)
    {
        $request->validate([
            'description' => 'nullable|string|max:1000',
            'target_market' => 'nullable|string',
            'revenue_model' => 'nullable|string'
        ]);

        $user = Auth::user();
        $project = Projet::firstOrCreate(['user_id' => $user->id], ['nom_projet' => $user->company_name ?? '']);
        $project->update([
            'description' => $request->description,
            'cibles' => $request->target_market ? [$request->target_market] : [],
            'modeles_revenus' => $request->revenue_model ? [$request->revenue_model] : [],
        ]);

        return response()->json(['success' => true]);
    }

    public function deleteProfile(Request $request)
    {
        $user = Auth::user();
        
        try {
            \DB::beginTransaction();
            
            // Supprimer toutes les données associées à l'utilisateur
            // Messages des conversations (suppression en cascade)
            foreach ($user->conversations as $conversation) {
                $conversation->messages()->delete();
            }
            
            // Conversations de l'utilisateur
            $user->conversations()->delete();
            
            // Analytics de l'utilisateur
            $user->analytics()->delete();
            
            // Projets de l'utilisateur  
            $user->projets()->delete();
            
            // Mémoires vectorielles si la table existe
            try {
                if (\Schema::hasTable('vector_memories')) {
                    \DB::table('vector_memories')->where('user_id', $user->id)->delete();
                }
            } catch (\Exception $e) {
                // Table n'existe pas, on continue
                Log::debug("Vector memories table does not exist, skipping");
            }
            
            // Supprimer les données Pinecone si configuré
            try {
                $pineconeService = app(\App\Services\PineconeService::class);
                // Supprimer les vecteurs de l'utilisateur dans tous les namespaces
                $namespaces = ['diagnostics', 'conversations', 'opportunites'];
                foreach ($namespaces as $namespace) {
                    $pineconeService->deleteByMetadata([
                        'user_id' => $user->id
                    ], $namespace);
                }
            } catch (\Exception $e) {
                Log::warning("Could not delete Pinecone vectors for user {$user->id}: " . $e->getMessage());
            }
            
            // Capturer l'ID avant suppression pour les logs
            $userId = $user->id;
            
            // Supprimer l'utilisateur
            $user->delete();
            
            \DB::commit();
            
            // Déconnecter et nettoyer la session
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            Log::info("User account deleted successfully", ['user_id' => $userId]);
            
            return response()->json([
                'success' => true, 
                'redirect' => route('landing'),
                'message' => 'Votre compte a été supprimé avec succès.'
            ]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error("Failed to delete user account for user {$user->id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression de votre compte. Veuillez réessayer.'
            ], 500);
        }
    }

    public function runDiagnostic(Request $request)
    {
        $user = Auth::user();

        // Vérifier si l'utilisateur peut lancer un diagnostic
        if (!$user->canRunDiagnostic()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez atteint la limite hebdomadaire de 5 diagnostics.'
            ], 429);
        }

        // NE PAS comptabiliser le diagnostic ici - seulement en cas de succès

        try {
            // Récupérer les données du projet de l'utilisateur
            $projet = Projet::where('user_id', $user->id)->first();
            
            if (!$projet || !$projet->isOnboardingComplete()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Veuillez d\'abord compléter votre profil entrepreneurial.',
                    'redirect' => route('onboarding.step1')
                ], 400);
            }

            // Préparer les données d'onboarding pour l'analyse
            $onboardingData = [
                'business_name' => $projet->nom_projet,
                'business_sector' => $projet->secteurs[0] ?? null,
                'business_stage' => $projet->maturite,
                'description' => $projet->description,
                'target_market' => $projet->cibles,
                'revenue_model' => $projet->modeles_revenus,
                'funding_stage' => $projet->stade_financement,
                'team_size' => $projet->taille_equipe,
                'location' => $projet->region,
                'formalized' => $projet->formalise === 'oui',
                'num_founders_male' => $projet->nombre_fondateurs,
                'num_founders_female' => $projet->nombre_fondatrices,
                'age_ranges' => $projet->tranches_age_fondateurs,
                'support_needs' => $projet->types_soutien,
                'created_at' => $projet->created_at->toISOString()
            ];

            // Lancer l'analyse avec le UserAnalyticsService
            $analyticsService = app(UserAnalyticsService::class);
            $analyticsService->updateEntrepreneurProfile($user, $onboardingData);
            
            // Générer la structure complète du dashboard
            $analyticsService->generateDashboardAnalytics($user);
            
            // Générer les insights complets
            $insights = $analyticsService->generateUserInsights($user);
            
            // Vectorize the final diagnostic only once at the end
            $analytics = \App\Models\UserAnalytics::where('user_id', $user->id)->first();
            if ($analytics) {
                $autoVectorService = app(\App\Services\AutoVectorizationService::class);
                $autoVectorService->vectorizeDiagnostic($analytics);
            }
            
            // MAINTENANT comptabiliser le diagnostic car génération réussie
            if (!$user->useDiagnostic()) {
                Log::warning("Could not use diagnostic for user {$user->id} after successful generation");
            }
            
            Log::info("Diagnostic completed for user {$user->id}", [
                'remaining_diagnostics' => $user->getRemainingDiagnostics()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Diagnostic généré avec succès.',
                'remaining' => $user->getRemainingDiagnostics(),
                'insights' => $insights
            ]);
            
        } catch (\Exception $e) {
            Log::error("Diagnostic failed for user {$user->id}: " . $e->getMessage());
            
            // PAS de comptabilisation en cas d'erreur
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du diagnostic. Veuillez réessayer.',
                'error_details' => config('app.debug') ? $e->getMessage() : null,
                'can_retry' => true,
                'remaining' => $user->getRemainingDiagnostics() // Pas décompté
            ], 500);
        }
    }

    /**
     * Envoyer un OTP pour la validation du changement d'email
     */
    public function sendEmailChangeOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email,' . auth()->id()
        ]);

        $user = Auth::user();
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Stocker l'OTP en session
        session([
            'email_change_new_email' => $request->email,
            'email_change_otp' => $otp,
            'email_change_otp_expires_at' => now()->addMinutes(10),
            'email_change_user_id' => $user->id
        ]);
        
        try {
            Mail::to($request->email)->send(new \App\Mail\OtpMail(
                $otp, 
                $user->name,
                'Validation de votre nouvelle adresse email'
            ));
            
            Log::info("Email change OTP sent to {$request->email} for user {$user->id}");
            
            return response()->json([
                'success' => true,
                'message' => 'Un code de vérification a été envoyé à votre nouvelle adresse email.'
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to send email change OTP to {$request->email}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de l\'email de vérification. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * Vérifier l'OTP et confirmer le changement d'email
     */
    public function verifyEmailChange(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6'
        ]);

        $sessionOtp = session('email_change_otp');
        $otpExpiresAt = session('email_change_otp_expires_at');
        $newEmail = session('email_change_new_email');
        $userId = session('email_change_user_id');

        // Vérifications de base
        if (!$sessionOtp || !$otpExpiresAt || !$newEmail || !$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Session expirée. Veuillez recommencer la procédure.'
            ], 400);
        }

        if (now()->gt($otpExpiresAt)) {
            session()->forget(['email_change_otp', 'email_change_otp_expires_at', 'email_change_new_email', 'email_change_user_id']);
            return response()->json([
                'success' => false,
                'message' => 'Code OTP expiré. Veuillez en demander un nouveau.'
            ], 400);
        }

        if ($request->otp !== $sessionOtp) {
            return response()->json([
                'success' => false,
                'message' => 'Code OTP incorrect.'
            ], 400);
        }

        // Vérifier que l'utilisateur connecté correspond
        if (Auth::id() != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Session invalide. Veuillez vous reconnecter.'
            ], 401);
        }

        try {
            // Mettre à jour l'email de l'utilisateur
            $user = Auth::user();
            $oldEmail = $user->email;
            
            $user->update([
                'email' => $newEmail,
                'email_verified_at' => now()
            ]);

            // Nettoyer la session
            session()->forget(['email_change_otp', 'email_change_otp_expires_at', 'email_change_new_email', 'email_change_user_id']);

            Log::info("Email changed successfully for user {$user->id} from {$oldEmail} to {$newEmail}");

            return response()->json([
                'success' => true,
                'message' => 'Votre adresse email a été mise à jour avec succès.',
                'new_email' => $newEmail
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to update email for user {$userId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'email. Veuillez réessayer.'
            ], 500);
        }
    }
}