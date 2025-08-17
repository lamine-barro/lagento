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
            'raison_sociale' => 'nullable|string|max:255',
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
        $user = Auth::user();
        $projet = Projet::where('user_id', $user->id)->latest()->first();
        
        // S'assurer que step 1 est complété
        if (!$projet || empty($projet->nom_projet)) {
            return redirect()->route('onboarding.step1')->with('warning', 'Veuillez d\'abord compléter l\'étape 1.');
        }
        
        return view('onboarding.step2');
    }
    
    public function processStep2(Request $request)
    {
        $request->validate([
            'telephone' => 'required|string|max:30',
            'email' => 'required|email',
            'site_web' => 'nullable|url',
            'nom_representant' => 'required|string|max:255',
            'role_representant' => 'required|string|max:255',
            'reseaux_instagram' => 'nullable|url',
            'reseaux_youtube' => 'nullable|url',
            'reseaux_x' => 'nullable|url',
            'reseaux_tiktok' => 'nullable|url',
            'reseaux_linkedin' => 'nullable|url',
            'reseaux_facebook' => 'nullable|url',
            'reseaux_whatsapp' => 'nullable|url',
        ]);

        $user = Auth::user();
        $projet = Projet::where('user_id', $user->id)->latest()->first();
        if (!$projet) {
            return redirect()->route('onboarding.step1');
        }
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
            'nom_representant' => $request->nom_representant,
            'role_representant' => $request->role_representant,
            'reseaux_sociaux' => array_filter($social),
        ]);

        return redirect()->route('onboarding.step3');
    }
    
    public function showStep3()
    {
        $user = Auth::user();
        $projet = Projet::where('user_id', $user->id)->latest()->first();
        
        // S'assurer que step 1 est complété
        if (!$projet || empty($projet->nom_projet)) {
            return redirect()->route('onboarding.step1')->with('warning', 'Veuillez d\'abord compléter l\'étape 1.');
        }
        
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
        $projet = Projet::where('user_id', $user->id)->latest()->first();
        if (!$projet) {
            return redirect()->route('onboarding.step1');
        }
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
        $user = Auth::user();
        $projet = Projet::where('user_id', $user->id)->latest()->first();
        
        // S'assurer que step 1 est complété
        if (!$projet || empty($projet->nom_projet)) {
            return redirect()->route('onboarding.step1')->with('warning', 'Veuillez d\'abord compléter l\'étape 1.');
        }
        
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
        $projet = Projet::where('user_id', $user->id)->latest()->first();
        if (!$projet) {
            return redirect()->route('onboarding.step1');
        }
        $projet->update([
            'taille_equipe' => $request->team_size,
            'nombre_fondateurs' => (int)$request->founders_count,
            'nombre_fondatrices' => (int)$request->female_founders_count,
            'tranches_age_fondateurs' => $request->age_ranges ?: [],
            'localisation_fondateurs' => strtolower((string)$request->founders_location),
            'structures_accompagnement' => $request->support_structures ?: [],
            'types_soutien' => $request->support_types ?: [],
            'details_besoins' => $request->additional_info,
        ]);

        // Vérifier si l'onboarding est complet
        if ($projet->isOnboardingComplete()) {
            $this->analyticsService->updateEntrepreneurProfile($user, [
                'projet_id' => $projet->id,
                'termine_le' => now()->toISOString(),
            ]);
            
            return redirect()->route('dashboard')->with('success', 'Félicitations ! Votre profil est maintenant complet.');
        } else {
            // Rediriger vers l'étape manquante
            $progress = $projet->getOnboardingProgress();
            
            if (!$progress['steps']['step1']) {
                return redirect()->route('onboarding.step1')->with('warning', 'Veuillez compléter les informations de votre entreprise.');
            }
            
            if (!$progress['steps']['step4']) {
                return redirect()->route('onboarding.step4')->with('warning', 'Veuillez compléter les informations sur votre équipe.');
            }
            
            // Si toutes les étapes semblent complètes mais isOnboardingComplete() retourne false
            return redirect()->route('onboarding.step1')->with('error', 'Veuillez vérifier et compléter toutes les informations obligatoires.');
        }
    }
}