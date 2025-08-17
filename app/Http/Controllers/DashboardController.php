<?php

namespace App\Http\Controllers;

use App\Models\Opportunite;
use App\Models\Projet;
use App\Models\UserAnalytics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
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
            $userAnalytics = UserAnalytics::where('user_id', $user->id)->first();
            
            // Conversations récentes
            $recentConversations = \App\Models\UserConversation::where('user_id', $user->id)
                ->latest()
                ->limit(3)
                ->get();
            
            return view('dashboard', compact(
                'opportunities', 
                'recentProjects', 
                'userAnalytics',
                'recentConversations'
            ));
            
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une vue avec des données vides
            return view('dashboard', [
                'opportunities' => collect(),
                'recentProjects' => collect(),
                'userAnalytics' => null,
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
                'message' => 'Vous avez atteint la limite mensuelle de 3 diagnostics.'
            ], 429);
        }

        // Utiliser un diagnostic
        if (!$user->useDiagnostic()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de lancer le diagnostic.'
            ], 500);
        }

        // TODO: Ici on appellerait l'agent de diagnostic/analytics
        // Pour l'instant, on simule juste le succès
        
        return response()->json([
            'success' => true,
            'message' => 'Diagnostic lancé avec succès.',
            'remaining' => $user->getRemainingDiagnostics()
        ]);
    }
}