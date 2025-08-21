@extends('layouts.app')

@section('title', 'Intelligence')
@section('page_title', 'Intelligence - Tableau de bord des projets | Agento')
@section('seo_title', 'Intelligence - Tableau de bord des projets entrepreneuriaux | Agento')
@section('meta_description', 'Découvrez tous les projets entrepreneuriaux avec onboarding complet. Filtrez par secteur, région, maturité et localisation des fondateurs pour trouver des opportunités de collaboration.')
@section('meta_keywords', 'projets entrepreneurs, startups côte ivoire, tableau de bord, intelligence business, réseau entrepreneurial')
@section('canonical_url', route('intelligence'))
@section('og_title', 'Intelligence - Tableau de bord des projets | Agento')
@section('og_description', 'Tableau de bord intelligent des projets entrepreneuriaux ivoiriens avec filtres avancés par secteur, région et maturité.')

@section('content')
<div class="container max-w-7xl mx-auto section px-4" x-data="intelligenceApp()">
    <!-- En-tête -->
    <div class="mb-6">
        <h1 class="text-primary mb-2">Intelligence</h1>
        <p class="text-secondary">Découvrez les projets entrepreneuriaux avec profils complets</p>
    </div>

    <!-- Bloc de filtres optimisé -->
    <div class="mb-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title flex items-center gap-2">
                    <i data-lucide="filter" class="w-5 h-5 text-orange"></i>
                    Filtres
                </h3>
            </div>
            <div class="card-body">
                <!-- Recherche -->
                <div class="mb-4">
                    <input 
                        type="text" 
                        x-model="filters.recherche"
                        @input.debounce.500ms="applyFilters()"
                        placeholder="Rechercher un projet..."
                        class="w-full px-3 py-2 border rounded-md text-sm"
                        style="border-color: var(--gray-300); background: var(--white);"
                    >
                </div>

                <!-- Dropdowns alignés -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Formalisation -->
                    <div>
                        <select 
                            x-model="filters.formalise"
                            @change="applyFilters()"
                            class="w-full px-3 py-2 border rounded-md text-sm"
                            style="border-color: var(--gray-300); background: var(--white);"
                        >
                            <option value="">Formalisation</option>
                            @foreach(['oui' => 'Formalisé', 'non' => 'Non formalisé'] as $key => $label)
                            <option value="{{ $key }}">{{ $label }} ({{ $filters['formalise'][$key] ?? 0 }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Région -->
                    <div>
                        <select 
                            x-model="filters.region"
                            @change="applyFilters()"
                            class="w-full px-3 py-2 border rounded-md text-sm"
                            style="border-color: var(--gray-300); background: var(--white);"
                        >
                            <option value="">Région</option>
                            @foreach(App\Constants\BusinessConstants::REGIONS as $regionKey => $coords)
                            <option value="{{ $regionKey }}">{{ $regionKey }} ({{ $filters['regions'][$regionKey] ?? 0 }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Secteur -->
                    <div>
                        <select 
                            x-model="filters.secteur"
                            @change="applyFilters()"
                            class="w-full px-3 py-2 border rounded-md text-sm"
                            style="border-color: var(--gray-300); background: var(--white);"
                        >
                            <option value="">Secteur</option>
                            @foreach(App\Constants\BusinessConstants::SECTEURS as $secteurKey => $secteurLabel)
                            <option value="{{ $secteurKey }}">{{ $secteurLabel }} ({{ $filters['secteurs'][$secteurKey] ?? 0 }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Filtres additionnels -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Maturité -->
                    <div>
                        <select 
                            x-model="filters.maturite"
                            @change="applyFilters()"
                            class="w-full px-3 py-2 border rounded-md text-sm"
                            style="border-color: var(--gray-300); background: var(--white);"
                        >
                            <option value="">Maturité</option>
                            @foreach(App\Constants\BusinessConstants::STADES_MATURITE as $maturiteKey => $maturiteLabel)
                            <option value="{{ $maturiteKey }}">{{ $maturiteLabel }} ({{ $filters['maturite'][$maturiteKey] ?? 0 }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Localisation des fondateurs -->
                    <div>
                        <select 
                            x-model="filters.localisation_fondateurs"
                            @change="applyFilters()"
                            class="w-full px-3 py-2 border rounded-md text-sm"
                            style="border-color: var(--gray-300); background: var(--white);"
                        >
                            <option value="">Localisation fondateurs</option>
                            @foreach(App\Constants\BusinessConstants::LOCALISATION_FONDATEURS as $locKey => $locLabel)
                            <option value="{{ $locKey }}">{{ $locLabel }} ({{ $filters['localisation_fondateurs'][$locKey] ?? 0 }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Reset filtres -->
                <div class="mt-4 text-center">
                    <button 
                        @click="resetFilters()" 
                        class="btn btn-ghost text-sm"
                    >
                        <i data-lucide="x" class="w-4 h-4 mr-2"></i>
                        Réinitialiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Layout principal -->
    <div class="grid lg:grid-cols-1 gap-6">

        <!-- Zone principale -->
        <div>
            <!-- En-tête avec contrôles -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-semibold" style="color: var(--gray-900);">
                        <span x-text="projets.total"></span> projets trouvés
                    </h2>
                    <p class="text-sm" style="color: var(--gray-600);">
                        Projets avec onboarding terminé uniquement
                    </p>
                </div>

                <!-- Tri -->
                <div class="flex items-center gap-2">
                    <label class="text-sm" style="color: var(--gray-700);">Trier par :</label>
                    <select 
                        x-model="sortBy" 
                        @change="applyFilters()"
                        class="px-3 py-1 border rounded text-sm"
                        style="border-color: var(--gray-300); background: var(--white);"
                    >
                        <option value="created_at">Date d'inscription</option>
                        <option value="nom_projet">Nom</option>
                        <option value="region">Région</option>
                        <option value="maturite">Maturité</option>
                    </select>
                    <select 
                        x-model="sortOrder" 
                        @change="applyFilters()"
                        class="px-3 py-1 border rounded text-sm"
                        style="border-color: var(--gray-300); background: var(--white);"
                    >
                        <option value="desc">Décroissant</option>
                        <option value="asc">Croissant</option>
                    </select>
                </div>
            </div>

            <!-- Cartes des projets -->
            <div x-show="!loading" class="grid md:grid-cols-2 gap-4 mb-8">
                @foreach($projets as $projet)
                <div class="intelligence-card">
                    <div class="flex items-start gap-4">
                        <!-- Logo -->
                        <div class="flex-shrink-0">
                            @if($projet->logo_url)
                                <img 
                                    src="{{ $projet->logo_url }}" 
                                    alt="Logo {{ $projet->nom_projet }}"
                                    class="w-12 h-12 rounded-lg object-cover"
                                    style="border: 1px solid var(--gray-200);"
                                >
                            @else
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center text-lg font-bold text-white"
                                     style="background: var(--orange);">
                                    {{ substr($projet->nom_projet, 0, 1) }}
                                </div>
                            @endif
                        </div>

                        <!-- Contenu -->
                        <div class="flex-1 min-w-0">
                            <!-- Nom du projet -->
                            <h3 class="font-semibold text-lg mb-1" style="color: var(--gray-900);">
                                {{ $projet->nom_projet }}
                            </h3>

                            <!-- Métadonnées -->
                            <div class="flex flex-wrap items-center gap-2 text-sm mb-3" style="color: var(--gray-600);">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3 h-3"></i>
                                    {{ $projet->region }}
                                </span>
                                <span>•</span>
                                @if($projet->secteurs && count($projet->secteurs) > 0)
                                <span class="flex items-center gap-1">
                                    <i data-lucide="tag" class="w-3 h-3"></i>
                                    {{ App\Constants\BusinessConstants::SECTEURS[$projet->secteurs[0]] ?? $projet->secteurs[0] }}
                                    @if(count($projet->secteurs) > 1)
                                        <span class="text-xs">+{{ count($projet->secteurs) - 1 }}</span>
                                    @endif
                                </span>
                                <span>•</span>
                                @endif
                                @if($projet->maturite)
                                <span class="flex items-center gap-1">
                                    <i data-lucide="trending-up" class="w-3 h-3"></i>
                                    {{ App\Constants\BusinessConstants::STADES_MATURITE[$projet->maturite] ?? $projet->maturite }}
                                </span>
                                @endif
                            </div>

                            <!-- Présentation du projet -->
                            @if($projet->description)
                            <div class="mb-3" x-data="{ expanded: false }">
                                <h4 class="text-sm font-medium mb-1" style="color: var(--gray-900);">Présentation du projet</h4>
                                <div class="text-sm" style="color: var(--gray-600);">
                                    <div x-show="!expanded">
                                        {{ Str::limit($projet->description, 100) }}
                                        @if(strlen($projet->description) > 100)
                                        <button @click="expanded = true" class="text-orange hover:underline ml-1">
                                            voir plus
                                        </button>
                                        @endif
                                    </div>
                                    <div x-show="expanded" x-transition>
                                        {{ $projet->description }}
                                        <button @click="expanded = false" class="text-orange hover:underline ml-1">
                                            voir moins
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Votre mot au président -->
                            @if($projet->mot_president)
                            <div class="mb-3" x-data="{ expanded: false }">
                                <h4 class="text-sm font-medium mb-1" style="color: var(--gray-900);">Votre mot au président</h4>
                                <div class="text-sm" style="color: var(--gray-600);">
                                    <div x-show="!expanded">
                                        {{ Str::limit($projet->mot_president, 100) }}
                                        @if(strlen($projet->mot_president) > 100)
                                        <button @click="expanded = true" class="text-orange hover:underline ml-1">
                                            voir plus
                                        </button>
                                        @endif
                                    </div>
                                    <div x-show="expanded" x-transition>
                                        {{ $projet->mot_president }}
                                        <button @click="expanded = false" class="text-orange hover:underline ml-1">
                                            voir moins
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Contact -->
                            @if($projet->nom_representant)
                            <div class="mb-3">
                                <p class="text-sm font-medium" style="color: var(--gray-900);">
                                    Contact : {{ $projet->nom_representant }}
                                </p>
                                <div class="flex flex-wrap items-center gap-3 text-sm" style="color: var(--gray-600);">
                                    @if($projet->email)
                                    <a href="mailto:{{ $projet->email }}" class="flex items-center gap-1 hover:text-orange transition-colors">
                                        <i data-lucide="mail" class="w-3 h-3"></i>
                                        {{ $projet->email }}
                                    </a>
                                    @endif
                                    @if($projet->telephone)
                                    <a href="tel:{{ $projet->telephone }}" class="flex items-center gap-1 hover:text-orange transition-colors">
                                        <i data-lucide="phone" class="w-3 h-3"></i>
                                        {{ $projet->telephone }}
                                    </a>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Date d'inscription -->
                            <div class="flex items-center text-xs" style="color: var(--gray-500);">
                                <i data-lucide="calendar" class="w-3 h-3 mr-1"></i>
                                Inscrit le {{ $projet->user ? $projet->user->created_at->format('d/m/Y') : $projet->created_at->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- État de chargement -->
            <div x-show="loading" x-transition class="grid md:grid-cols-2 gap-4">
                @for($i = 0; $i < 8; $i++)
                <div class="intelligence-card animate-pulse">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg" style="background: var(--gray-200);"></div>
                        <div class="flex-1">
                            <div class="h-5 rounded mb-2" style="background: var(--gray-200);"></div>
                            <div class="h-4 rounded mb-3 w-3/4" style="background: var(--gray-200);"></div>
                            <div class="h-3 rounded w-1/2" style="background: var(--gray-200);"></div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>

            <!-- Pagination -->
            <div class="flex justify-center">
                {{ $projets->links('components.pagination') }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function intelligenceApp() {
    return {
        loading: false,
        filters: {
            recherche: '{{ request("recherche") }}',
            formalise: '{{ request("formalise") }}',
            region: '{{ request("region") }}',
            secteur: '{{ request("secteur") }}',
            maturite: '{{ request("maturite") }}',
            localisation_fondateurs: '{{ request("localisation_fondateurs") }}'
        },
        sortBy: '{{ request("sort_by", "created_at") }}',
        sortOrder: '{{ request("sort_order", "desc") }}',
        projets: {
            total: {{ $projets->total() }}
        },

        applyFilters() {
            this.loading = true;
            
            // Construction des paramètres de requête
            const params = new URLSearchParams();
            
            Object.keys(this.filters).forEach(key => {
                if (this.filters[key]) {
                    params.set(key, this.filters[key]);
                }
            });
            
            if (this.sortBy) params.set('sort_by', this.sortBy);
            if (this.sortOrder) params.set('sort_order', this.sortOrder);
            
            // Redirection avec les paramètres
            window.location.href = '{{ route("intelligence") }}?' + params.toString();
        },

        resetFilters() {
            this.filters = {
                recherche: '',
                formalise: '',
                region: '',
                secteur: '',
                maturite: '',
                localisation_fondateurs: ''
            };
            this.sortBy = 'created_at';
            this.sortOrder = 'desc';
            this.applyFilters();
        }
    }
}
</script>
@endpush

@push('styles')
<style>
/* Styles pour la page Intelligence */
.intelligence-card {
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    padding: var(--space-4);
    transition: var(--transition);
}

.intelligence-card:hover {
    border-color: var(--gray-300);
}

/* Filtres */
.intelligence-filters input[type="radio"]:checked {
    accent-color: var(--orange);
}

.intelligence-filters .filter-count {
    background: var(--gray-100);
    color: var(--gray-600);
    font-size: var(--text-xs);
    padding: 2px 8px;
    border-radius: var(--radius-full);
}

/* Responsive */
@media (max-width: 1024px) {
    .intelligence-card {
        padding: var(--space-3);
    }
}

/* Animation de chargement */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Scrollbar pour les listes de filtres */
.max-h-48::-webkit-scrollbar {
    width: 4px;
}

.max-h-48::-webkit-scrollbar-track {
    background: var(--gray-100);
    border-radius: 2px;
}

.max-h-48::-webkit-scrollbar-thumb {
    background: var(--gray-300);
    border-radius: 2px;
}

.max-h-48::-webkit-scrollbar-thumb:hover {
    background: var(--gray-400);
}
</style>
@endpush

@endsection