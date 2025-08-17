<?php

namespace App\Http\Controllers;

use App\Services\UserAnalyticsService;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Laravel\Facades\Image;

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
            'nom_projet' => 'required|string|max:255',
            'raison_sociale' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'annee_creation' => 'nullable|integer|min:2010|max:' . date('Y'),
            'formalise' => 'required|in:OUI,NON',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg|max:10240',
            'region' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $user = Auth::user();
        $projet = Projet::firstOrCreate(['user_id' => $user->id, 'nom_projet' => $request->nom_projet]);

        $logoUrl = $projet->logo_url;
        if ($request->hasFile('logo')) {
            $image = Image::read($request->file('logo'));
            $image->scaleDown(1024, 1024); // limite dimensions
            $encoded = $image->toJpeg(quality: 80);
            $filename = 'logos/' . uniqid('logo_', true) . '.jpg';
            \Storage::disk('public')->put($filename, (string) $encoded);
            $logoUrl = '/storage/' . $filename;
        }

        $projet->update([
            'nom_projet' => $request->nom_projet,
            'raison_sociale' => $request->raison_sociale,
            'description' => $request->description,
            'annee_creation' => $request->annee_creation,
            'formalise' => strtolower($request->formalise),
            'logo_url' => $logoUrl,
            'region' => $request->region,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_public' => true,
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
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email',
            'site_web' => 'nullable|url',
            'nom_representant' => 'nullable|string|max:255',
            'role_representant' => 'nullable|string|max:255',
            'reseaux_instagram' => 'nullable|url',
            'reseaux_youtube' => 'nullable|url',
            'reseaux_x' => 'nullable|url',
            'reseaux_tiktok' => 'nullable|url',
            'reseaux_linkedin' => 'nullable|url',
            'reseaux_facebook' => 'nullable|url',
            'reseaux_whatsapp' => 'nullable|url',
        ]);

        $user = Auth::user();
        $projet = Projet::firstOrCreate(['user_id' => $user->id, 'nom_projet' => $user->company_name ?? '']);
        $social = [
            'instagram' => $request->reseaux_instagram,
            'youtube' => $request->reseaux_youtube,
            'x' => $request->reseaux_x,
            'tiktok' => $request->reseaux_tiktok,
            'linkedin' => $request->reseaux_linkedin,
            'facebook' => $request->reseaux_facebook,
            'whatsapp_business' => $request->reseaux_whatsapp,
        ];
        $projet->update([
            'telephone' => $request->telephone,
            'email' => $request->email,
            'site_web' => $request->site_web,
            'reseaux_sociaux' => array_filter($social),
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
            'secteurs' => 'nullable|array|max:5',
            'produits_services' => 'nullable|string|max:1000',
            'cibles' => 'nullable|array',
            'maturite' => 'nullable|string',
            'stade_financement' => 'nullable|string',
            'modeles_revenus' => 'nullable|array|max:5',
            'revenus' => 'nullable|string',
        ]);
        $user = Auth::user();
        $projet = Projet::firstOrCreate(['user_id' => $user->id, 'nom_projet' => $user->company_name ?? '']);
        $projet->update([
            'secteurs' => $request->secteurs ?: [],
            'produits_services' => $request->produits_services ? [$request->produits_services] : [],
            'cibles' => $request->cibles ?: [],
            'maturite' => $request->maturite,
            'stade_financement' => $request->stade_financement,
            'modeles_revenus' => $request->modeles_revenus ?: [],
            'revenus' => $request->revenus,
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
            'nombre_fondateurs' => 'required|integer|min:1',
            'nombre_fondatrices' => 'required|integer|min:0',
            'tranches_age_fondateurs' => 'nullable|array',
            'localisation_fondateurs' => 'nullable|string',
            'taille_equipe' => 'nullable|string',
            'structures_accompagnement' => 'nullable|array',
            'types_soutien' => 'nullable|array|max:3',
            'details_besoins' => 'nullable|string|max:1000'
        ]);

        $user = Auth::user();
        $projet = Projet::firstOrCreate(['user_id' => $user->id, 'nom_projet' => $user->company_name ?? '']);
        $projet->update([
            'taille_equipe' => $request->taille_equipe,
            'nombre_fondateurs' => (int)$request->nombre_fondateurs,
            'nombre_fondatrices' => (int)$request->nombre_fondatrices,
            'tranches_age_fondateurs' => $request->tranches_age_fondateurs ?: [],
            'localisation_fondateurs' => strtolower((string)$request->localisation_fondateurs),
            'structures_accompagnement' => $request->structures_accompagnement ?: [],
            'types_soutien' => $request->types_soutien ?: [],
            'details_besoins' => $request->details_besoins,
        ]);

        $this->analyticsService->updateEntrepreneurProfile($user, [
            'projet_id' => $projet->id,
            'termine_le' => now()->toISOString(),
        ]);

        return redirect()->route('dashboard');
    }
}