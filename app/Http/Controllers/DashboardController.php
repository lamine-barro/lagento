<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\Project;
use App\Models\UserAnalytics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get matched opportunities
        $opportunities = Opportunity::where('business_sector', $user->business_sector)
            ->orWhere('business_sector', 'all')
            ->where('deadline', '>=', now())
            ->latest()
            ->limit(5)
            ->get();
        
        // Get user's project
        $project = Project::where('user_id', $user->id)->first();
        
        // Get user statistics
        $analytics = UserAnalytics::where('user_id', $user->id)->first();
        
        $stats = [
            'messages_sent' => $analytics->messages_sent ?? 0,
            'documents_generated' => $analytics->documents_generated ?? 0,
            'opportunities_matched' => $analytics->opportunities_matched ?? 0,
            'time_saved' => $analytics->time_saved_hours ?? 0 . 'h'
        ];
        
        return view('dashboard', compact('opportunities', 'project', 'stats'));
    }
    
    public function profile()
    {
        $user = Auth::user();
        $project = Project::where('user_id', $user->id)->first();
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
        $project = Project::firstOrCreate(['user_id' => $user->id], ['project_name' => '']);
        $project->update([
            'products_services' => json_encode(['text' => $request->description]),
            'targets' => json_encode([$request->target_market]),
            'revenue_models' => json_encode([$request->revenue_model])
        ]);

        return response()->json(['success' => true]);
    }
}