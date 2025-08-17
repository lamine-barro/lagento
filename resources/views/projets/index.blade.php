@extends('layouts.app')

@section('seo_title', 'Annuaire des Projets Entrepreneuriaux - Côte d\'Ivoire | LAgentO')
@section('meta_description', 'Découvrez les projets innovants d\'entrepreneurs ivoiriens. Trouvez des partenaires, investisseurs et opportunités de collaboration dans l\'écosystème startup de Côte d\'Ivoire.')
@section('meta_keywords', 'projets startup côte ivoire, entrepreneurs ivoiriens, annuaire entreprises ci, innovation abidjan, partenaires business afrique')
@section('meta_robots', 'index, follow')
@section('og_title', 'Annuaire des Projets Entrepreneuriaux en Côte d\'Ivoire')
@section('og_description', 'Plus de {{ $projets->total() }} projets entrepreneuriaux référencés en Côte d\'Ivoire. Connectez-vous avec l\'écosystème innovation ivoirien.')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2" style="color: var(--gray-900);">
            Annuaire des Projets
        </h1>
        <p style="color: var(--gray-700);">
            Découvrez les projets entrepreneuriaux de la Côte d'Ivoire
        </p>
    </div>

    <!-- Filtres de recherche -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Recherche textuelle -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                    Rechercher
                </label>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Nom du projet, description..."
                    class="input-field w-full"
                />
            </div>

            <!-- Région -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                    Région
                </label>
                <select name="region" class="input-field w-full">
                    <option value="">Toutes les régions</option>
                    @foreach(config('constants.REGIONS') as $region => $coords)
                        <option value="{{ $region }}" {{ request('region') == $region ? 'selected' : '' }}>
                            {{ $region }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Secteur -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                    Secteur
                </label>
                <select name="secteur" class="input-field w-full">
                    <option value="">Tous les secteurs</option>
                    @foreach(config('constants.SECTEURS') as $key => $value)
                        <option value="{{ $key }}" {{ request('secteur') == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Maturité -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                    Maturité
                </label>
                <select name="maturite" class="input-field w-full">
                    <option value="">Tous les stades</option>
                    @foreach(config('constants.STADES_MATURITE') as $key => $value)
                        <option value="{{ $key }}" {{ request('maturite') == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Boutons -->
            <div class="md:col-span-4 flex gap-3">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                    Rechercher
                </button>
                <a href="{{ route('projets.index') }}" class="btn btn-ghost">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <!-- Résultats -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <p style="color: var(--gray-700);">
                {{ $projets->total() }} projet(s) trouvé(s)
            </p>
            @if(request()->hasAny(['search', 'region', 'secteur', 'maturite']))
                <p class="text-sm mt-1" style="color: var(--gray-500);">
                    @if(request('search'))
                        Recherche : "{{ request('search') }}"
                    @endif
                    @if(request('region'))
                        • Région : {{ request('region') }}
                    @endif
                    @if(request('secteur'))
                        • Secteur : {{ config('constants.SECTEURS')[request('secteur')] ?? request('secteur') }}
                    @endif
                </p>
            @endif
        </div>
        <a href="{{ route('projets.create') }}" class="btn btn-primary">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Ajouter mon projet
        </a>
    </div>

    <!-- Grille des projets -->
    @if($projets->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            @foreach($projets as $projet)
                <div class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                    <!-- Header avec logo -->
                    <div class="p-6 border-b">
                        <div class="flex items-start gap-4">
                            @if($projet->logo_url)
                                <img 
                                    src="{{ Storage::url($projet->logo_url) }}" 
                                    alt="Logo {{ $projet->nom_projet }}"
                                    class="w-16 h-16 rounded-lg object-cover"
                                />
                            @else
                                <div class="w-16 h-16 rounded-lg flex items-center justify-center text-white text-xl font-bold" style="background: var(--orange-primary);">
                                    {{ strtoupper(substr($projet->nom_projet, 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold mb-1" style="color: var(--gray-900);">
                                    {{ $projet->nom_projet }}
                                </h3>
                                <p class="text-sm" style="color: var(--gray-600);">
                                    {{ $projet->region }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu -->
                    <div class="p-6">
                        <p class="text-sm mb-4 line-clamp-3" style="color: var(--gray-700);">
                            {{ Str::limit($projet->description, 120) }}
                        </p>

                        <!-- Tags secteurs -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            @foreach(array_slice($projet->secteurs_labels, 0, 2) as $secteur)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $secteur }}
                                </span>
                            @endforeach
                            @if(count($projet->secteurs_labels) > 2)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    +{{ count($projet->secteurs_labels) - 2 }}
                                </span>
                            @endif
                        </div>

                        <!-- Badges status -->
                        <div class="flex gap-2 mb-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $projet->getMaturiteLabel() }}
                            </span>
                            @if($projet->is_verified)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white" style="background: var(--orange-primary);">
                                    <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                    Vérifié
                                </span>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <a 
                                href="{{ route('projets.show', $projet) }}" 
                                class="btn btn-primary flex-1 text-center"
                            >
                                Voir le projet
                            </a>
                            @if($projet->email || $projet->telephone)
                                <button class="btn btn-ghost px-3" title="Contact disponible">
                                    <i data-lucide="mail" class="w-4 h-4"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            {{ $projets->appends(request()->query())->links() }}
        </div>
    @else
        <!-- Aucun résultat -->
        <div class="text-center py-12">
            <div class="text-gray-400 mb-4">
                <i data-lucide="search" class="w-16 h-16 mx-auto"></i>
            </div>
            <h3 class="text-lg font-medium mb-2" style="color: var(--gray-900);">
                Aucun projet trouvé
            </h3>
            <p class="mb-6" style="color: var(--gray-600);">
                Essayez d'ajuster vos critères de recherche
            </p>
            <a href="{{ route('projets.create') }}" class="btn btn-primary">
                Soyez le premier à ajouter votre projet
            </a>
        </div>
    @endif
</div>
@endsection