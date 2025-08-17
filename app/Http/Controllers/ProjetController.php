<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProjetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Afficher la liste des projets publics (annuaire)
     */
    public function index(Request $request)
    {
        $query = Projet::public()->verified()->with('user');

        // Filtres de recherche
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('region')) {
            $query->byRegion($request->region);
        }

        if ($request->filled('secteur')) {
            $query->bySecteur($request->secteur);
        }

        if ($request->filled('maturite')) {
            $query->byMaturite($request->maturite);
        }

        $projets = $query->latest('last_updated_at')->paginate(12);

        return view('projets.index', compact('projets'));
    }

    /**
     * Afficher les projets de l'utilisateur connecté
     */
    public function mesProjects()
    {
        $projets = Auth::user()->projets()->latest()->paginate(10);
        return view('projets.mes-projets', compact('projets'));
    }

    /**
     * Afficher le formulaire de création d'un nouveau projet
     */
    public function create()
    {
        return view('projets.create');
    }

    /**
     * Enregistrer un nouveau projet
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Identité
            'nom_projet' => 'required|string|max:255',
            'raison_sociale' => 'nullable|string|max:255',
            'description' => 'required|string|min:50',
            'logo' => 'nullable|image|max:2048',
            
            // Formalisation
            'formalise' => 'required|in:oui,non',
            'annee_creation' => 'nullable|string',
            'numero_rccm' => 'nullable|string|max:50',
            
            // Activité
            'secteurs' => 'required|array|min:1',
            'secteurs.*' => 'string',
            'produits_services' => 'required|array|min:1',
            'produits_services.*' => 'string|max:200',
            'cibles' => 'required|array|min:1',
            'cibles.*' => 'string',
            
            // Développement
            'maturite' => 'required|string',
            'stade_financement' => 'required|string',
            'modeles_revenus' => 'required|array|min:1',
            'modeles_revenus.*' => 'string',
            'revenus' => 'required|string',
            
            // Localisation
            'region' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            
            // Contact
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'site_web' => 'nullable|url|max:255',
            'reseaux_sociaux' => 'nullable|array',
            
            // Équipe
            'nombre_fondateurs' => 'required|string',
            'nombre_fondatrices' => 'required|string',
            'tranches_age_fondateurs' => 'required|array|min:1',
            'tranches_age_fondateurs.*' => 'string',
            'localisation_fondateurs' => 'required|string',
            'taille_equipe' => 'required|string',
            
            // Besoins
            'structures_accompagnement' => 'nullable|array',
            'structures_accompagnement.*' => 'string',
            'types_soutien' => 'required|array|min:1',
            'types_soutien.*' => 'string',
            'details_besoins' => 'nullable|string|max:1000',
            'abonne_newsletter' => 'boolean',
            
            // Meta
            'is_public' => 'boolean'
        ]);

        // Upload du logo si présent
        if ($request->hasFile('logo')) {
            $image = Image::read($request->file('logo'));
            $image->scaleDown(1024, 1024);
            $encoded = $image->toJpeg(quality: 80);
            $filename = 'projets/logos/' . uniqid('logo_', true) . '.jpg';
            Storage::disk('public')->put($filename, (string) $encoded);
            $validated['logo_url'] = $filename;
        }

        // Nettoyer les réseaux sociaux (supprimer les champs vides)
        if (isset($validated['reseaux_sociaux'])) {
            $validated['reseaux_sociaux'] = array_filter($validated['reseaux_sociaux'], function($value) {
                return !empty(trim($value));
            });
        }

        // Créer le projet
        $projet = Auth::user()->projets()->create($validated);

        return redirect()->route('projets.show', $projet)
            ->with('success', 'Projet créé avec succès !');
    }

    /**
     * Afficher un projet spécifique
     */
    public function show(Projet $projet)
    {
        // Vérifier la visibilité
        if (!$projet->is_public && $projet->user_id !== Auth::id()) {
            abort(404);
        }

        return view('projets.show', compact('projet'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Projet $projet)
    {
        // Vérifier les permissions
        if ($projet->user_id !== Auth::id()) {
            abort(403);
        }

        return view('projets.edit', compact('projet'));
    }

    /**
     * Mettre à jour un projet
     */
    public function update(Request $request, Projet $projet)
    {
        // Vérifier les permissions
        if ($projet->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            // Mêmes règles que store() mais moins strictes pour update
            'nom_projet' => 'required|string|max:255',
            'raison_sociale' => 'nullable|string|max:255',
            'description' => 'required|string|min:50',
            'logo' => 'nullable|image|max:2048',
            
            'formalise' => 'required|in:oui,non',
            'annee_creation' => 'nullable|string',
            'numero_rccm' => 'nullable|string|max:50',
            
            'secteurs' => 'required|array|min:1',
            'produits_services' => 'required|array|min:1',
            'cibles' => 'required|array|min:1',
            
            'maturite' => 'required|string',
            'stade_financement' => 'required|string',
            'modeles_revenus' => 'required|array|min:1',
            'revenus' => 'required|string',
            
            'region' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'site_web' => 'nullable|url|max:255',
            'reseaux_sociaux' => 'nullable|array',
            
            'nombre_fondateurs' => 'required|string',
            'nombre_fondatrices' => 'required|string',
            'tranches_age_fondateurs' => 'required|array|min:1',
            'localisation_fondateurs' => 'required|string',
            'taille_equipe' => 'required|string',
            
            'structures_accompagnement' => 'nullable|array',
            'types_soutien' => 'required|array|min:1',
            'details_besoins' => 'nullable|string|max:1000',
            'abonne_newsletter' => 'boolean',
            
            'is_public' => 'boolean'
        ]);

        // Upload du nouveau logo si présent
        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo
            if ($projet->logo_url) {
                Storage::disk('public')->delete($projet->logo_url);
            }
            $image = Image::read($request->file('logo'));
            $image->scaleDown(1024, 1024);
            $encoded = $image->toJpeg(quality: 80);
            $filename = 'projets/logos/' . uniqid('logo_', true) . '.jpg';
            Storage::disk('public')->put($filename, (string) $encoded);
            $validated['logo_url'] = $filename;
        }

        // Nettoyer les réseaux sociaux
        if (isset($validated['reseaux_sociaux'])) {
            $validated['reseaux_sociaux'] = array_filter($validated['reseaux_sociaux'], function($value) {
                return !empty(trim($value));
            });
        }

        $projet->update($validated);

        return redirect()->route('projets.show', $projet)
            ->with('success', 'Projet mis à jour avec succès !');
    }

    /**
     * Supprimer un projet
     */
    public function destroy(Projet $projet)
    {
        // Vérifier les permissions
        if ($projet->user_id !== Auth::id()) {
            abort(403);
        }

        // Supprimer le logo s'il existe
        if ($projet->logo_url) {
            Storage::disk('public')->delete($projet->logo_url);
        }

        $projet->delete();

        return redirect()->route('projets.mes-projets')
            ->with('success', 'Projet supprimé avec succès !');
    }

    /**
     * API : Recherche de projets pour autocomplete/suggestions
     */
    public function search(Request $request)
    {
        $term = $request->get('q', '');
        
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $projets = Projet::public()
            ->where(function($query) use ($term) {
                $query->where('nom_projet', 'LIKE', "%{$term}%")
                      ->orWhere('description', 'LIKE', "%{$term}%");
            })
            ->select('id', 'nom_projet', 'description', 'region', 'secteurs')
            ->limit(10)
            ->get();

        return response()->json($projets);
    }

    /**
     * Basculer la visibilité publique d'un projet
     */
    public function toggleVisibility(Projet $projet)
    {
        if ($projet->user_id !== Auth::id()) {
            abort(403);
        }

        $projet->update(['is_public' => !$projet->is_public]);

        $status = $projet->is_public ? 'public' : 'privé';
        
        return back()->with('success', "Le projet est maintenant {$status}.");
    }
}