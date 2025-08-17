<?php

namespace App\Http\Controllers;

use App\Services\UserAnalyticsService;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    private UserAnalyticsService $analyticsService;

    public function __construct(UserAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }
    public function showStep1()
    {
        return view('onboarding.step1');
    }
    
    public function processStep1(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'year' => 'nullable|integer|min:2010|max:' . date('Y'),
            'formalized' => 'required|in:OUI,NON',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg|max:10240',
            'region' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $user = Auth::user();
        $project = Project::firstOrCreate(['user_id' => $user->id], ['project_name' => $request->project_name]);

        $logoUrl = $project->logo_url;
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $logoUrl = '/storage/' . $path;
        }

        $project->update([
            'project_name' => $request->project_name,
            'company_name' => $request->company_name,
            'description' => $request->description,
            'incorporation_year' => $request->year,
            'formalized' => strtolower($request->formalized),
            'logo_url' => $logoUrl,
            'region' => $request->region,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route('onboarding.step2');
    }
    
    public function showStep2()
    {
        return view('onboarding.step2');
    }
    
    public function processStep2(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'rep_name' => 'nullable|string|max:255',
            'rep_role' => 'nullable|string|max:255',
            'social_instagram' => 'nullable|url',
            'social_youtube' => 'nullable|url',
            'social_x' => 'nullable|url',
            'social_tiktok' => 'nullable|url',
            'social_linkedin' => 'nullable|url',
            'social_facebook' => 'nullable|url',
            'social_whatsapp' => 'nullable|url',
        ]);

        $user = Auth::user();
        $project = Project::firstOrCreate(['user_id' => $user->id], ['project_name' => '']);
        $social = [
            'instagram' => $request->social_instagram,
            'youtube' => $request->social_youtube,
            'x' => $request->social_x,
            'tiktok' => $request->social_tiktok,
            'linkedin' => $request->social_linkedin,
            'facebook' => $request->social_facebook,
            'whatsapp_business' => $request->social_whatsapp, // map vers clé canonique
        ];
        $project->update([
            'phone' => $request->phone,
            'email' => $request->email,
            'website' => $request->website,
            'social_links' => json_encode($social),
        ]);

        return redirect()->route('onboarding.step3');
    }
    
    public function showStep3()
    {
        // Activity & Development
        return view('onboarding.step3');
    }

    public function processStep3(Request $request)
    {
        $request->validate([
            'business_sector_multi' => 'nullable|array|max:5',
            'products' => 'nullable|string|max:1000',
            'target_clients' => 'nullable|array',
            'business_stage' => 'nullable|string',
            'funding_stage' => 'nullable|string',
            'revenue_models' => 'nullable|array|max:5',
            'monthly_revenue' => 'nullable|string',
        ]);
        $user = Auth::user();
        $project = Project::firstOrCreate(['user_id' => $user->id], ['project_name' => '']);
        $project->update([
            'sectors' => json_encode($request->business_sector_multi),
            'products_services' => json_encode(['text' => $request->products]),
            'targets' => json_encode($request->target_clients),
            'maturity' => $request->business_stage,
            'funding_stage' => $request->funding_stage,
            'revenue_models' => json_encode($request->revenue_models),
            'revenue_range' => $request->monthly_revenue,
        ]);

        return redirect()->route('onboarding.step4');
    }

    public function showStep4()
    {
        // Team & Support
        return view('onboarding.step4');
    }

    public function processStep4(Request $request)
    {
        $request->validate([
            'founders_count' => 'required|integer|min:1',
            'female_founders_count' => 'required|integer|min:0',
            'age_ranges' => 'nullable|array',
            'founders_location' => 'nullable|string',
            'team_size' => 'nullable|string',
            'support_structures' => 'nullable|array',
            'support_types' => 'nullable|array|max:3',
            'additional_info' => 'nullable|string|max:1000'
        ]);

        $user = Auth::user();
        $project = Project::firstOrCreate(['user_id' => $user->id], ['project_name' => '']);
        $project->update([
            'team_size' => $request->team_size,
            'num_founders_male' => max(0, (int)$request->founders_count - (int)$request->female_founders_count),
            'num_founders_female' => (int)$request->female_founders_count,
            'founder_age_ranges' => json_encode($request->age_ranges),
            'founder_location' => strtolower((string)$request->founders_location),
            'support_structures' => json_encode($request->support_structures),
            'support_types' => json_encode($request->support_types),
            'needs_details' => $request->additional_info,
        ]);

        $this->analyticsService->updateEntrepreneurProfile($user, [
            'project_id' => $project->id,
            'completed_at' => now()->toISOString(),
        ]);

        return redirect()->route('dashboard');
    }
}