<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IntelligenceController extends Controller
{
    public function index()
    {
        return view('intelligence.index');
    }

    public function projects(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $filter = $request->get('filter', 'tous');
            $search = $request->get('search', '');

            $query = Projet::with('user')
                ->whereHas('user', function ($query) {
                    $query->where('onboarding_completed', true);
                });

            // Appliquer les filtres
            switch ($filter) {
                case 'formalise':
                    $query->where('formalise', 'oui');
                    break;
                case 'non_formalise':
                    $query->where('formalise', 'non');
                    break;
                case 'recent':
                    $query->where('created_at', '>=', now()->subDays(30));
                    break;
            }

            // Appliquer la recherche
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('secteurs', 'like', '%' . $search . '%')
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', '%' . $search . '%');
                      });
                });
            }

            $projets = $query->orderBy('created_at', 'desc')
                           ->paginate(20, ['*'], 'page', $page);

            return view('intelligence.projects', compact('projets'))->render();
        } catch (\Exception $e) {
            \Log::error('Intelligence projects error: ' . $e->getMessage());
            return '<div class="bg-red-50 border border-red-200 rounded-lg p-4"><p class="text-red-600">Erreur lors du chargement des projets</p></div>';
        }
    }
}