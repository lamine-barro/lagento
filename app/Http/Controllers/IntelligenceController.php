<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IntelligenceController extends Controller
{
    // Le middleware auth est géré par les routes

    public function index()
    {
        return view('intelligence.index');
    }

    public function insights()
    {
        try {
            // Statistiques agrégées nationales
            $stats = [
                'total_projets' => Projet::count(),
                'total_entrepreneurs' => User::whereHas('projets')->count(),
                'projets_formalises' => Projet::where('formalise', 'oui')->count(),
                'projets_non_formalises' => Projet::where('formalise', 'non')->count(),
            ];

            // Répartition par région
            $regions = Projet::select('region', DB::raw('count(*) as total'))
                ->whereNotNull('region')
                ->groupBy('region')
                ->orderBy('total', 'desc')
                ->get();

            // Répartition par secteur
            $secteurs = Projet::whereNotNull('secteurs')
                ->get()
                ->flatMap(function ($projet) {
                    return $projet->secteurs ?? [];
                })
                ->countBy()
                ->sortDesc()
                ->take(10);

            // Répartition par stade de maturité
            $maturites = Projet::select('maturite', DB::raw('count(*) as total'))
                ->whereNotNull('maturite')
                ->groupBy('maturite')
                ->orderBy('total', 'desc')
                ->get();

            // Répartition par stade de financement
            $financements = Projet::select('stade_financement', DB::raw('count(*) as total'))
                ->whereNotNull('stade_financement')
                ->groupBy('stade_financement')
                ->orderBy('total', 'desc')
                ->get();

            // Évolution mensuelle des créations de projets
            $evolution = Projet::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

            return view('intelligence.insights', compact(
                'stats', 'regions', 'secteurs', 'maturites', 'financements', 'evolution'
            ))->render();
        } catch (\Exception $e) {
            \Log::error('Intelligence insights error: ' . $e->getMessage());
            return '<div class="bg-red-50 border border-red-200 rounded-lg p-4"><p class="text-red-600">Erreur lors du chargement des insights</p></div>';
        }
    }

    public function directory()
    {
        try {
            $projets = Projet::with('user')
                ->whereHas('user', function ($query) {
                    $query->where('onboarding_completed', true);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return view('intelligence.directory', compact('projets'))->render();
        } catch (\Exception $e) {
            \Log::error('Intelligence directory error: ' . $e->getMessage());
            return '<div class="bg-red-50 border border-red-200 rounded-lg p-4"><p class="text-red-600">Erreur lors du chargement de l\'annuaire</p></div>';
        }
    }
}