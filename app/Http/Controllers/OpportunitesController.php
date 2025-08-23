<?php

namespace App\Http\Controllers;

use App\Models\Opportunite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OpportunitesController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = 30; // Pagination par 30 éléments
            
            // Commencer la requête avec les opportunités ouvertes
            $query = Opportunite::where('statut', 'Ouvert');

            // Filtrer par type si spécifié
            $selectedType = $request->get('type');
            if ($selectedType && $selectedType !== 'tous') {
                $query->where('type', $selectedType);
            }

            // Filtrer par type d'institution si spécifié
            $selectedInstitutionType = $request->get('institution_type');
            if ($selectedInstitutionType && $selectedInstitutionType !== 'tous') {
                $query->where('institution_type', $selectedInstitutionType);
            }

            // Recherche textuelle si spécifiée
            $search = $request->get('search');
            if ($search) {
                $query->search($search);
            }

            // Ordonner par date de création (plus récent en premier)
            $query->orderBy('created_at', 'desc');

            // Paginer les résultats
            $opportunities = $query->paginate($perPage);

            // Obtenir les statistiques pour les filtres
            $typeStats = Opportunite::getTypeStats();
            $institutionTypeStats = Opportunite::getInstitutionTypeStats();

            // Préparer les données pour la vue
            return view('opportunites.index', [
                'opportunities' => $opportunities,
                'typeStats' => $typeStats,
                'institutionTypeStats' => $institutionTypeStats,
                'selectedType' => $selectedType ?? 'tous',
                'selectedInstitutionType' => $selectedInstitutionType ?? 'tous',
                'search' => $search,
                'totalOpportunities' => Opportunite::where('statut', 'Ouvert')->count(),
                'currentPage' => $opportunities->currentPage(),
                'totalPages' => $opportunities->lastPage(),
                'hasMorePages' => $opportunities->hasMorePages()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des opportunités', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('opportunites.index', [
                'opportunities' => collect([]),
                'typeStats' => [],
                'institutionTypeStats' => [],
                'selectedType' => 'tous',
                'selectedInstitutionType' => 'tous',
                'search' => '',
                'totalOpportunities' => 0,
                'currentPage' => 1,
                'totalPages' => 1,
                'hasMorePages' => false,
                'error' => 'Erreur lors du chargement des opportunités'
            ]);
        }
    }
}