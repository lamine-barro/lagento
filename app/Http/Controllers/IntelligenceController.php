<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IntelligenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('intelligence.index');
    }

    public function insights()
    {
        // Statistiques agrégées nationales
        $stats = [
            'total_projets' => Projet::count(),
            'total_entrepreneurs' => User::whereHas('projet')->count(),
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
        ));
    }

    public function directory()
    {
        $projets = Projet::with('user')
            ->whereHas('user', function ($query) {
                $query->where('onboarding_completed', true);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('intelligence.directory', compact('projets'));
    }
}