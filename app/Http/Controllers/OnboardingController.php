<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use App\Services\ContentValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Laravel\Facades\Image;

class OnboardingController extends Controller
{
    protected ContentValidationService $validator;
    
    public function __construct(ContentValidationService $validator)
    {
        $this->validator = $validator;
    }
    public function showStep1()
    {
        $user = Auth::user();
        \Log::info('Showing step 1', ['user_id' => $user ? $user->id : 'null']);
        $projet = Projet::where('user_id', $user->id)->latest()->first();
        return view('onboarding.step1', compact('projet'));
    }
    
    public function processStep1(Request $request)
    {
        \Log::info('Step 1 form submitted', [
            'request_data' => $request->all(),
            'user_id' => auth()->id()
        ]);

        $request->validate([
            'nom_projet' => 'required|string|max:255',
            'raison_sociale' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'annee_creation' => 'nullable|integer|min:2010|max:' . date('Y'),
            'formalise' => 'required|in:oui,non',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg|max:10240',
            'region' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        \Log::info('Step 1 validation passed, starting LLM validation');

        // Validation LLM pour détecter les faux contenus
        try {
            $validation = $this->validator->validateOnboardingStep($request->all(), 'step1');
            \Log::info('LLM validation result', ['validation' => $validation]);
            
            if (!$validation['valid']) {
                \Log::info('LLM validation failed, redirecting back');
                return back()
                    ->withErrors(['content_validation' => 'Les informations saisies semblent être des données factices. ' . ($validation['reason'] ?? 'Veuillez fournir des informations réelles sur votre projet.')])
                    ->withInput();
            }
        } catch (\Exception $e) {
            \Log::error('LLM validation error: ' . $e->getMessage());
            // Continue without LLM validation if there's an error
        }

        $user = Auth::user();
        
        // Utiliser updateOrCreate pour éviter les problèmes de persistance
        $projet = Projet::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nom_projet' => $request->nom_projet,
                'raison_sociale' => $request->raison_sociale,
                'description' => $request->description,
                'annee_creation' => $request->annee_creation,
                'formalise' => $request->formalise,
                'region' => $request->region,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_public' => true,
            ]
        );

        // Gérer le logo si présent
        if ($request->hasFile('logo')) {
            try {
                $originalSize = $request->file('logo')->getSize();
                \Log::info('Starting logo upload process', [
                    'file_name' => $request->file('logo')->getClientOriginalName(),
                    'original_size' => $originalSize,
                    'user_id' => $request->user()->id
                ]);
                
                // Optimiser avec Intervention Image pour réduire la taille
                $image = Image::read($request->file('logo'));
                $image->scaleDown(800, 600); // Redimensionner pour réduire la taille
                $encoded = $image->toJpeg(quality: 60); // Qualité plus basse pour fichier plus léger
                
                \Log::info('Logo optimized', [
                    'original_size' => $originalSize,
                    'optimized_size' => strlen($encoded),
                    'compression_ratio' => round((1 - strlen($encoded) / $originalSize) * 100, 2) . '%'
                ]);
                
                // Use centralized file storage service
                $fileStorage = app(\App\Services\FileStorageService::class);
                $result = $fileStorage->storeLogo($encoded, $request->user()->id);
                
                \Log::info('Logo uploaded successfully', [
                    'url' => $result['url'],
                    'storage' => $result['storage']
                ]);
                
                $projet->logo_url = $result['url'];
                $projet->save();
                
            } catch (\Exception $e) {
                \Log::error('Logo upload failed', [
                    'error' => $e->getMessage(),
                    'user_id' => $request->user()->id,
                    'file_name' => $request->file('logo')->getClientOriginalName()
                ]);
                
                // Continue without logo instead of failing completely
                return back()->withErrors([
                    'logo' => 'Erreur lors du téléchargement du logo. Vous pouvez continuer sans logo et l\'ajouter plus tard.'
                ])->withInput();
            }
        }

        \Log::info('Step 1 completed successfully, redirecting to step 2', [
            'projet_id' => $projet->id,
            'user_id' => $user->id
        ]);

        // Sauvegarde de progression effectuée, poursuivre
        return redirect()->route('onboarding.step2')->with('progress_saved', true);
    }
    
    public function showStep2()
    {
        $user = Auth::user();
        $projet = Projet::where('user_id', $user->id)->latest()->first();
        
        // S'assurer que step 1 est complété
        if (!$projet || empty($projet->nom_projet)) {
            return redirect()->route('onboarding.step1')->with('warning', 'Veuillez d\'abord compléter l\'étape 1.');
        }
        
        return view('onboarding.step2', compact('projet'));
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

        // Validation LLM pour détecter les faux contenus
        $validation = $this->validator->validateOnboardingStep($request->all(), 'step2');
        
        if (!$validation['valid']) {
            return back()
                ->withErrors(['content_validation' => 'Les informations de contact saisies semblent être des données factices. ' . ($validation['reason'] ?? 'Veuillez fournir des informations de contact réelles.')])
                ->withInput();
        }

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

        return redirect()->route('onboarding.step3')->with('progress_saved', true);
    }
    
    public function showStep3()
    {
        $user = Auth::user();
        $projet = Projet::where('user_id', $user->id)->latest()->first();
        
        // S'assurer que step 1 est complété
        if (!$projet || empty($projet->nom_projet)) {
            return redirect()->route('onboarding.step1')->with('warning', 'Veuillez d\'abord compléter l\'étape 1.');
        }
        
        return view('onboarding.step3', compact('projet'));
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

        // Validation LLM pour détecter les faux contenus
        $validation = $this->validator->validateOnboardingStep($request->all(), 'step3');
        
        if (!$validation['valid']) {
            return back()
                ->withErrors(['content_validation' => 'Les informations business saisies semblent être des données factices. ' . ($validation['reason'] ?? 'Veuillez fournir des informations réelles sur votre activité.')])
                ->withInput();
        }

        $user = Auth::user();
        $projet = Projet::where('user_id', $user->id)->latest()->first();
        if (!$projet) {
            return redirect()->route('onboarding.step1');
        }
        $projet->update([
            'secteurs' => $request->secteurs ?: [],
            'produits_services' => $request->produits_services ? explode(',', $request->produits_services) : [],
            'cibles' => $request->cibles ?: [],
            'maturite' => $request->maturite,
            'stade_financement' => $request->stade_financement,
            'modeles_revenus' => $request->modeles_revenus ?: [],
            'revenus' => $request->revenus,
        ]);

        return redirect()->route('onboarding.step4')->with('progress_saved', true);
    }

    public function showStep4()
    {
        $user = Auth::user();
        $projet = Projet::where('user_id', $user->id)->latest()->first();
        
        // S'assurer que step 1 est complété
        if (!$projet || empty($projet->nom_projet)) {
            return redirect()->route('onboarding.step1')->with('warning', 'Veuillez d\'abord compléter l\'étape 1.');
        }
        
        return view('onboarding.step4', compact('projet'));
    }

    public function processStep4(Request $request)
    {
        \Log::info('Step 4 form submitted', [
            'request_data' => $request->all(),
            'user_id' => auth()->id()
        ]);

        $request->validate([
            'founders_count' => 'required|integer|min:1',
            'female_founders_count' => 'required|integer|min:0',
            'age_ranges' => 'nullable|array',
            'founders_location' => 'nullable|in:local,diaspora,mixte,LOCAL,DIASPORA,MIXTE',
            'team_size' => 'nullable|string',
            'support_structures' => 'nullable|array',
            'support_types' => 'nullable|array|max:5',
            'additional_info' => 'nullable|string|max:1000'
        ]);

        // Validation LLM pour détecter les faux contenus
        $validation = $this->validator->validateOnboardingStep($request->all(), 'step4');
        
        if (!$validation['valid']) {
            return back()
                ->withErrors(['content_validation' => 'Les informations d\'équipe saisies semblent être des données factices. ' . ($validation['reason'] ?? 'Veuillez fournir des informations réelles sur votre équipe.')])
                ->withInput();
        }

        \Log::info('Step 4 validation passed');

        $user = Auth::user();
        $projet = Projet::where('user_id', $user->id)->latest()->first();
        if (!$projet) {
            // Si aucun projet n'existe, vérifier les champs obligatoires de l'étape 1
            \Log::warning('No project found for user at step 4', ['user_id' => $user->id]);
            return redirect()->route('onboarding.step1')
                ->with('warning', 'Veuillez compléter les étapes précédentes avant de continuer.');
        }

        // Vérifier que les étapes précédentes sont complétées
        $progress = $projet->getOnboardingProgress();
        if (!$progress['steps']['step1']) {
            \Log::warning('Step 1 not completed, redirecting', ['user_id' => $user->id]);
            return redirect()->route('onboarding.step1')
                ->with('warning', 'Veuillez d\'abord compléter l\'étape 1.');
        }

        $projet->update([
            'taille_equipe' => $request->team_size,
            'nombre_fondateurs' => (int)$request->founders_count,
            'nombre_fondatrices' => (int)$request->female_founders_count,
            'tranches_age_fondateurs' => $request->age_ranges ?: [],
            'localisation_fondateurs' => $request->founders_location ? strtolower($request->founders_location) : null,
            'structures_accompagnement' => $request->support_structures ?: [],
            'types_soutien' => $request->support_types ?: [],
            'mot_president' => $request->additional_info,
        ]);

        // Rafraîchir le modèle pour être sûr d'avoir les dernières données
        $projet->refresh();

        // Vérifier que l'onboarding est vraiment complet avant de marquer comme terminé
        if ($projet->isOnboardingComplete()) {
            // Marquer l'onboarding comme terminé
            $user->update(['onboarding_completed' => true]);

            \Log::info('Step 4 completed successfully', [
                'projet_id' => $projet->id,
                'user_onboarding_completed' => true,
                'redirecting_to' => 'diagnostic'
            ]);

            // Redirection vers la page diagnostic
            return redirect()->route('diagnostic')->with('success', 'Félicitations ! Votre profil est maintenant complet. Vous pouvez maintenant lancer votre diagnostic.');
        } else {
            \Log::warning('Onboarding not complete after step 4', [
                'user_id' => $user->id,
                'progress' => $projet->getOnboardingProgress()
            ]);
            
            // Rediriger vers la première étape incomplète
            if (!$progress['steps']['step1']) {
                return redirect()->route('onboarding.step1');
            } elseif (!$progress['steps']['step2']) {
                return redirect()->route('onboarding.step2');
            } elseif (!$progress['steps']['step3']) {
                return redirect()->route('onboarding.step3');
            } else {
                return redirect()->route('onboarding.step4');
            }
        }
    }
}