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
            'email' => 'required|email',
            'phone' => 'nullable|string|max:30',
        ]);

        $user = Auth::user();
        $user->update($request->only(['name', 'email', 'phone']));

        return response()->json(['success' => true]);
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
}