<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use App\Constants\BusinessConstants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class IntelligenceController extends Controller
{
    public function index(Request $request)
    {
        // Construction de la requête de base pour les projets avec onboarding complet
        $query = Projet::whereNotNull('nom_projet')
            ->whereNotNull('formalise')
            ->whereNotNull('region')
            ->whereNotNull('nombre_fondateurs')
            ->whereNotNull('nombre_fondatrices')
            ->where('is_public', true)
            ->with('user'); // Pour récupérer la date d'inscription

        // Application des filtres
        if ($request->filled('recherche')) {
            $recherche = $request->input('recherche');
            $query->where(function ($q) use ($recherche) {
                $q->where('nom_projet', 'LIKE', "%{$recherche}%")
                  ->orWhere('description', 'LIKE', "%{$recherche}%");
            });
        }

        if ($request->filled('formalise')) {
            $query->where('formalise', $request->input('formalise'));
        }

        if ($request->filled('region')) {
            $query->where('region', $request->input('region'));
        }

        if ($request->filled('secteur')) {
            $query->whereJsonContains('secteurs', $request->input('secteur'));
        }

        if ($request->filled('maturite')) {
            $query->where('maturite', $request->input('maturite'));
        }

        if ($request->filled('localisation_fondateurs')) {
            $query->where('localisation_fondateurs', $request->input('localisation_fondateurs'));
        }

        // Tri
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        $allowedSorts = ['nom_projet', 'region', 'maturite', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $projets = $query->paginate(20)->withQueryString();

        // Calcul des compteurs pour les filtres (mis en cache)
        $filters = $this->getFilters();

        return view('intelligence.index', compact('projets', 'filters'));
    }

    public function getFilters()
    {
        return Cache::remember('intelligence_filters', 300, function () {
            // Requête de base pour les projets éligibles
            $baseQuery = Projet::whereNotNull('nom_projet')
                ->whereNotNull('formalise')
                ->whereNotNull('region')
                ->whereNotNull('nombre_fondateurs')
                ->whereNotNull('nombre_fondatrices')
                ->where('is_public', true);

            return [
                'formalise' => $baseQuery->clone()
                    ->select('formalise', DB::raw('count(*) as count'))
                    ->groupBy('formalise')
                    ->pluck('count', 'formalise')
                    ->toArray(),

                'regions' => $baseQuery->clone()
                    ->select('region', DB::raw('count(*) as count'))
                    ->groupBy('region')
                    ->pluck('count', 'region')
                    ->toArray(),

                'secteurs' => $this->getSecteursCounts($baseQuery),

                'maturite' => $baseQuery->clone()
                    ->select('maturite', DB::raw('count(*) as count'))
                    ->groupBy('maturite')
                    ->whereNotNull('maturite')
                    ->pluck('count', 'maturite')
                    ->toArray(),

                'localisation_fondateurs' => $baseQuery->clone()
                    ->select('localisation_fondateurs', DB::raw('count(*) as count'))
                    ->groupBy('localisation_fondateurs')
                    ->whereNotNull('localisation_fondateurs')
                    ->pluck('count', 'localisation_fondateurs')
                    ->toArray(),
            ];
        });
    }

    private function getSecteursCounts($baseQuery)
    {
        // Récupération de tous les projets avec leurs secteurs
        $projets = $baseQuery->clone()
            ->select('secteurs')
            ->whereNotNull('secteurs')
            ->get();

        $secteursCounts = [];

        foreach ($projets as $projet) {
            $secteurs = $projet->secteurs ?? [];
            foreach ($secteurs as $secteur) {
                $secteursCounts[$secteur] = ($secteursCounts[$secteur] ?? 0) + 1;
            }
        }

        return $secteursCounts;
    }

    public function apiFilters()
    {
        return response()->json($this->getFilters());
    }
}