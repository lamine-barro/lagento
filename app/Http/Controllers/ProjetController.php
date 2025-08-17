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