<?php

namespace App\Http\Controllers;

use App\Models\Opportunite;
use App\Models\Projet;
use App\Models\UserAnalytics;
use App\Services\UserAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DiagnosticController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        try {
            // Get matched opportunities
            $opportunities = collect(); // Empty collection by default
            if ($user->business_sector) {
                $opportunities = Opportunite::where('business_sector', $user->business_sector)
                    ->orWhere('business_sector', 'all')
                    ->where('deadline', '>=', now())
                    ->latest()
                    ->limit(5)
                    ->get();
            }
            
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
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_public' => $request->boolean('is_public'),
                'email_notifications' => $request->boolean('email_notifications'),
                'email_verified_at' => null, // Réinitialiser la vérification
            ]);
            
            // TODO: Envoyer un nouveau code OTP à la nouvelle adresse
            // Ici vous pourriez appeler votre service d'envoi d'OTP
            
            return response()->json([
                'success' => true, 
                'email_changed' => true,
                'message' => 'Profil mis à jour. Un code de vérification a été envoyé à votre nouvelle adresse email.'
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
        
        // Supprimer toutes les données associées à l'utilisateur
        $user->conversations()->delete();
        $user->projets()->delete();
        $user->userAnalytics()->delete();
        
        // Supprimer l'utilisateur
        $user->delete();
        
        // Déconnecter et rediriger
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return response()->json(['success' => true, 'redirect' => route('landing')]);
    }

    public function runDiagnostic(Request $request)
    {
        $user = Auth::user();

        // Vérifier si l'utilisateur peut lancer un diagnostic
        if (!$user->canRunDiagnostic()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez atteint la limite mensuelle de 50 diagnostics.'
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
}