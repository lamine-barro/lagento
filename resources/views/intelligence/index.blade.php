@extends('layouts.app')

@section('title', 'Projets Entrepreneuriaux - République de Côte d\'Ivoire')

@push('styles')
<style>
/* Harmonisation avec le design existant */
.projects-page {
    background: var(--gray-50);
}

.projects-header {
    background: var(--white);
    border-bottom: 1px solid var(--gray-200);
}

.projects-card {
    background: var(--white);
    border: 1px solid var(--gray-200);
    color: var(--gray-900);
}

.project-card {
    background: var(--white);
    border: 1px solid var(--gray-200);
    transition: all 0.2s ease;
}

.project-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    border-color: var(--orange-primary);
}

.filter-active {
    background: linear-gradient(135deg, var(--orange-primary), var(--success-700));
    color: var(--white);
}

.filter-inactive {
    color: var(--gray-600);
    background: transparent;
    border: 1px solid var(--gray-300);
}

.filter-inactive:hover {
    color: var(--gray-900);
    background: var(--gray-100);
    border-color: var(--gray-400);
}

/* Animations */
.project-card {
    animation: fadeInUp 0.6s ease-out forwards;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .filter-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: var(--space-2);
    }
}
</style>
@endpush

@section('content')
<div class="projects-page min-h-screen">
    <!-- Header harmonisé -->
    <div class="projects-header sticky top-16 z-40 shadow-sm">
        <div class="container max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16 md:h-20">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 md:h-12 md:w-12 rounded-lg flex items-center justify-center" 
                         style="background: linear-gradient(135deg, var(--orange-primary), var(--success-700));">
                        <svg class="h-6 w-6 md:h-8 md:w-8" style="color: var(--white);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg md:text-2xl font-bold" style="color: var(--gray-900);">Projets Entrepreneuriaux</h1>
                        <p class="text-xs md:text-sm hidden sm:block" style="color: var(--gray-600);">Annuaire National des Entrepreneurs</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 60 40'%3E%3Crect width='20' height='40' fill='%23ff8c00'/%3E%3Crect x='20' width='20' height='40' fill='%23ffffff'/%3E%3Crect x='40' width='20' height='40' fill='%2300b04f'/%3E%3C/svg%3E" alt="Drapeau CI" class="h-4 w-6 md:h-6 md:w-9 rounded border">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs md:text-sm font-semibold" style="color: var(--gray-900);">République de Côte d'Ivoire</p>
                        <p class="text-xs" style="color: var(--gray-600);">Union - Discipline - Travail</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="container max-w-7xl mx-auto px-4 pt-6 md:pt-8" x-data="{ 
        filter: 'tous',
        searchQuery: '',
        currentPage: 1,
        perPage: 20
    }">
        
        <!-- Filtres et recherche -->
        <div class="projects-card rounded-xl shadow-sm p-4 md:p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <!-- Barre de recherche -->
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" 
                               x-model="searchQuery"
                               placeholder="Rechercher un projet ou entrepreneur..."
                               class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
                    </div>
                </div>

                <!-- Filtres -->
                <div class="flex flex-wrap gap-2">
                    <button @click="filter = 'tous'" 
                            :class="filter === 'tous' ? 'filter-active' : 'filter-inactive'"
                            class="px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200">
                        Tous
                    </button>
                    <button @click="filter = 'formalise'" 
                            :class="filter === 'formalise' ? 'filter-active' : 'filter-inactive'"
                            class="px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200">
                        Formalisés
                    </button>
                    <button @click="filter = 'non_formalise'" 
                            :class="filter === 'non_formalise' ? 'filter-active' : 'filter-inactive'"
                            class="px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200">
                        Non formalisés
                    </button>
                    <button @click="filter = 'recent'" 
                            :class="filter === 'recent' ? 'filter-active' : 'filter-inactive'"
                            class="px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200">
                        Récents
                    </button>
                </div>
            </div>
        </div>

        <!-- Liste des projets -->
        <div id="projects-content">
            <div class="text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-r from-orange-400 to-green-400 mb-4">
                    <svg class="animate-spin h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <p class="text-lg font-medium text-gray-900 mb-2">Chargement des projets</p>
                <p class="text-sm text-gray-600">Compilation de l'annuaire...</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chargement initial des projets
    loadProjects();
    
    async function loadProjects(page = 1, filter = 'tous', search = '') {
        try {
            const params = new URLSearchParams({
                page: page,
                filter: filter,
                search: search
            });
            
            const response = await fetch(`/intelligence/projects?${params}`);
            const html = await response.text();
            document.getElementById('projects-content').innerHTML = html;
        } catch (error) {
            console.error('Error loading projects:', error);
            document.getElementById('projects-content').innerHTML = 
                `<div class="text-center py-12">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-6 max-w-md mx-auto">
                        <svg class="w-12 h-12 mx-auto text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-red-900 mb-2">Erreur de chargement</h3>
                        <p class="text-red-700">Impossible de charger les projets</p>
                    </div>
                </div>`;
        }
    }
    
    // Écouter les changements de filtres
    document.addEventListener('alpine:init', () => {
        Alpine.data('projectsPage', () => ({
            filter: 'tous',
            searchQuery: '',
            currentPage: 1,
            perPage: 20,
            
            init() {
                this.$watch('filter', (filter) => {
                    this.currentPage = 1;
                    loadProjects(this.currentPage, filter, this.searchQuery);
                });
                
                this.$watch('searchQuery', (search) => {
                    this.currentPage = 1;
                    // Debounce search
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        loadProjects(this.currentPage, this.filter, search);
                    }, 500);
                });
            }
        }));
    });
    
    // Fonction globale pour la pagination
    window.changePage = function(page) {
        const alpineData = Alpine.$data(document.querySelector('[x-data]'));
        alpineData.currentPage = page;
        loadProjects(page, alpineData.filter, alpineData.searchQuery);
    };
});
</script>
@endpush
@endsection