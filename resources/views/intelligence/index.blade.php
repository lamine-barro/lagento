@extends('layouts.app')

@section('title', 'Intelligence Économique - République de Côte d\'Ivoire')

@push('styles')
<style>
/* Harmonisation avec le design existant */
.intelligence-page {
    background: var(--gray-50);
}

.intelligence-header {
    background: var(--white);
    border-bottom: 1px solid var(--gray-200);
}

.intelligence-card {
    background: var(--white);
    border: 1px solid var(--gray-200);
    color: var(--gray-900);
}

.intelligence-nav-active {
    background: linear-gradient(135deg, var(--orange-primary), var(--success-700));
    color: var(--white);
}

.intelligence-nav-inactive {
    color: var(--gray-600);
    background: transparent;
}

.intelligence-nav-inactive:hover {
    color: var(--gray-900);
    background: var(--gray-100);
}

/* Animations */
.stat-card {
    animation: fadeInUp 0.6s ease-out forwards;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .stat-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: var(--space-3);
    }
    
    .map-container {
        height: 250px;
    }
}
</style>
@endpush

@section('content')
<div class="intelligence-page min-h-screen">
    <!-- Header harmonisé -->
    <div class="intelligence-header sticky top-16 z-40 shadow-sm">
        <div class="container max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16 md:h-20">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 md:h-12 md:w-12 rounded-lg flex items-center justify-center" 
                         style="background: linear-gradient(135deg, var(--orange-primary), var(--success-700));">
                        <svg class="h-6 w-6 md:h-8 md:w-8" style="color: var(--white);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg md:text-2xl font-bold" style="color: var(--gray-900);">Intelligence Économique</h1>
                        <p class="text-xs md:text-sm hidden sm:block" style="color: var(--gray-600);">Observatoire National de l'Entrepreneuriat</p>
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

    <!-- Navigation Pills Mobile -->
    <div class="container max-w-7xl mx-auto px-4 pt-6 md:pt-8" x-data="{ activeTab: 'insights' }">
        <div class="intelligence-card rounded-xl shadow-sm p-2 mb-6">
            <nav class="flex space-x-1">
                <button @click="activeTab = 'insights'" :class="activeTab === 'insights' ? 'intelligence-nav-active' : 'intelligence-nav-inactive'" class="flex-1 flex items-center justify-center px-3 py-3 rounded-lg font-medium transition-all duration-200 text-sm">
                    <svg class="w-4 h-4 mr-1 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="hidden sm:inline">Insights</span>
                    <span class="sm:hidden">Données</span>
                </button>
                <button @click="activeTab = 'directory'" :class="activeTab === 'directory' ? 'intelligence-nav-active' : 'intelligence-nav-inactive'" class="flex-1 flex items-center justify-center px-3 py-3 rounded-lg font-medium transition-all duration-200 text-sm">
                    <svg class="w-4 h-4 mr-1 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 515.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="hidden sm:inline">Annuaire</span>
                    <span class="sm:hidden">Projets</span>
                </button>
                <button @click="activeTab = 'map'" :class="activeTab === 'map' ? 'intelligence-nav-active' : 'intelligence-nav-inactive'" class="flex-1 flex items-center justify-center px-3 py-3 rounded-lg font-medium transition-all duration-200 text-sm">
                    <svg class="w-4 h-4 mr-1 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    <span class="hidden sm:inline">Carte</span>
                    <span class="sm:hidden">Map</span>
                </button>
            </nav>
        </div>

        <!-- Content Areas -->
        <div x-show="activeTab === 'insights'" x-transition.opacity.duration.300ms>
            <div id="insights-content">
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-r from-orange-400 to-green-400 mb-4">
                        <svg class="animate-spin h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <p class="text-lg font-medium text-gray-900 mb-2">Chargement des insights</p>
                    <p class="text-sm text-gray-600">Analyse des données nationales...</p>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'directory'" x-transition.opacity.duration.300ms>
            <div id="directory-content">
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-r from-orange-400 to-green-400 mb-4">
                        <svg class="animate-spin h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <p class="text-lg font-medium text-gray-900 mb-2">Chargement de l'annuaire</p>
                    <p class="text-sm text-gray-600">Compilation des projets...</p>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'map'" x-transition.opacity.duration.300ms>
            <div id="map-content">
                <div class="intelligence-card rounded-xl shadow-sm overflow-hidden">
                    <div class="p-4" style="border-bottom: 1px solid var(--gray-200);">
                        <h3 class="text-lg font-semibold" style="color: var(--gray-900);">Répartition Géographique</h3>
                        <p class="text-sm" style="color: var(--gray-600);">Projets entrepreneuriaux par région</p>
                    </div>
                    <div class="p-4">
                        <div id="map-container" class="w-full h-64 md:h-96 rounded-lg" style="background: var(--gray-100);">
                            <div class="flex items-center justify-center h-full">
                                <div class="text-center">
                                    <svg class="w-12 h-12 mx-auto mb-4" style="color: var(--gray-400);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                    </svg>
                                    <p style="color: var(--gray-600);">Carte interactive en cours de chargement...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
<link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet" />

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuration Mapbox corrigée
    console.log('Mapbox token check:', '{{ config('services.mapbox.token') }}');
    mapboxgl.accessToken = '{{ config('services.mapbox.token') }}';
    
    if (!mapboxgl.accessToken) {
        console.error('Mapbox token is missing! Check your .env configuration.');
    }
    
    // State management
    window.intelligenceApp = {
        activeTab: 'insights',
        loaded: {
            insights: false,
            directory: false,
            map: false
        },
        
        init() {
            this.loadInsights();
            this.setupEventListeners();
        },
        
        setupEventListeners() {
            document.addEventListener('alpine:init', () => {
                Alpine.data('intelligencePage', () => ({
                    activeTab: 'insights',
                    
                    init() {
                        this.$watch('activeTab', (tab) => {
                            this.loadTab(tab);
                        });
                    },
                    
                    loadTab(tab) {
                        switch(tab) {
                            case 'insights':
                                if (!window.intelligenceApp.loaded.insights) {
                                    window.intelligenceApp.loadInsights();
                                }
                                break;
                            case 'directory':
                                if (!window.intelligenceApp.loaded.directory) {
                                    window.intelligenceApp.loadDirectory();
                                }
                                break;
                            case 'map':
                                if (!window.intelligenceApp.loaded.map) {
                                    window.intelligenceApp.loadMap();
                                }
                                break;
                        }
                    }
                }));
            });
        },
        
        async loadInsights() {
            try {
                const response = await fetch('/intelligence/insights');
                const html = await response.text();
                document.getElementById('insights-content').innerHTML = html;
                this.loaded.insights = true;
                
                // Initialize charts after content load
                setTimeout(() => this.initializeCharts(), 100);
            } catch (error) {
                console.error('Error loading insights:', error);
                document.getElementById('insights-content').innerHTML = 
                    '<div class="text-center py-12"><div class="bg-red-50 border border-red-200 rounded-lg p-6 max-w-md mx-auto"><svg class="w-12 h-12 mx-auto text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><h3 class="text-lg font-medium text-red-900 mb-2">Erreur de chargement</h3><p class="text-red-700">Impossible de charger les données d\'insights</p></div></div>';
            }
        },
        
        async loadDirectory() {
            try {
                const response = await fetch('/intelligence/directory');
                const html = await response.text();
                document.getElementById('directory-content').innerHTML = html;
                this.loaded.directory = true;
            } catch (error) {
                console.error('Error loading directory:', error);
                document.getElementById('directory-content').innerHTML = 
                    '<div class="text-center py-12"><div class="bg-red-50 border border-red-200 rounded-lg p-6 max-w-md mx-auto"><svg class="w-12 h-12 mx-auto text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><h3 class="text-lg font-medium text-red-900 mb-2">Erreur de chargement</h3><p class="text-red-700">Impossible de charger l\'annuaire des projets</p></div></div>';
            }
        },
        
        async loadMap() {
            try {
                // Charger les vraies données de la carte
                const response = await fetch('/intelligence/map-data');
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.initializeMapWithData(data.regions);
                    } else {
                        this.initializeMapFallback();
                    }
                } else {
                    this.initializeMapFallback();
                }
                this.loaded.map = true;
            } catch (error) {
                console.error('Error loading map:', error);
                this.initializeMapFallback();
                this.loaded.map = true;
            }
        },
        
        initializeMapWithData(regions) {
            if (!mapboxgl.accessToken) {
                this.initializeMapFallback();
                return;
            }
            
            try {
                const map = new mapboxgl.Map({
                    container: 'map-container',
                    style: 'mapbox://styles/mapbox/light-v11',
                    center: [-5.547, 7.540], // Centre exact de la Côte d'Ivoire
                    zoom: 6.5
                });
            
                map.on('load', () => {
                    regions.forEach(region => {
                        if (region.latitude && region.longitude) {
                            const marker = new mapboxgl.Marker({
                                color: this.getRegionColor(region.projects_count),
                                scale: Math.max(0.8, Math.min(1.5, region.projects_count / 20))
                            })
                            .setLngLat([region.longitude, region.latitude])
                            .setPopup(new mapboxgl.Popup().setHTML(
                                `<div class="p-3">
                                    <h4 class="font-semibold text-gray-900 mb-1">${region.name}</h4>
                                    <p class="text-sm text-gray-600">${region.projects_count} projet${region.projects_count > 1 ? 's' : ''}</p>
                                </div>`
                            ))
                            .addTo(map);
                        }
                    });
                });
                
                map.on('error', (e) => {
                    console.error('Mapbox error:', e);
                    this.initializeMapFallback();
                });
            } catch (error) {
                console.error('Map initialization error:', error);
                this.initializeMapFallback();
            }
        },
        
        initializeMapFallback() {
            console.log('Initializing map fallback with token:', mapboxgl.accessToken);
            
            if (!mapboxgl.accessToken) {
                document.getElementById('map-container').innerHTML = `
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <svg class="w-12 h-12 mx-auto mb-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-red-600 font-medium">Erreur de configuration Mapbox</p>
                            <p class="text-sm text-gray-500">Token d'accès manquant</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            // Fallback avec données par défaut des régions CI
            const defaultRegions = [
                { name: 'Abidjan', coords: [-4.0083, 5.3097], projects: 0 },
                { name: 'Bouaké', coords: [-5.0300, 7.6900], projects: 0 },
                { name: 'Yamoussoukro', coords: [-5.2767, 6.8206], projects: 0 },
                { name: 'San-Pédro', coords: [-6.6364, 4.7467], projects: 0 },
                { name: 'Korhogo', coords: [-5.6292, 9.4581], projects: 0 },
                { name: 'Daloa', coords: [-6.8770, 6.8770], projects: 0 }
            ];
            
            try {
                const map = new mapboxgl.Map({
                    container: 'map-container',
                    style: 'mapbox://styles/mapbox/light-v11',
                    center: [-5.547, 7.540],
                    zoom: 6.5
                });
            
                map.on('load', () => {
                    defaultRegions.forEach(region => {
                        const marker = new mapboxgl.Marker({
                            color: '#6B7280', // Couleur grise par défaut
                            scale: 0.8
                        })
                        .setLngLat(region.coords)
                        .setPopup(new mapboxgl.Popup().setHTML(
                            `<div class="p-3">
                                <h4 class="font-semibold text-gray-900 mb-1">${region.name}</h4>
                                <p class="text-sm text-gray-600">Données en cours de chargement...</p>
                            </div>`
                        ))
                        .addTo(map);
                    });
                });
                
                map.on('error', (e) => {
                    console.error('Mapbox error:', e);
                    document.getElementById('map-container').innerHTML = `
                        <div class="flex items-center justify-center h-full">
                            <div class="text-center">
                                <svg class="w-12 h-12 mx-auto mb-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-red-600 font-medium">Erreur de chargement de la carte</p>
                                <p class="text-sm text-gray-500">Vérifiez votre connexion internet</p>
                            </div>
                        </div>
                    `;
                });
            } catch (error) {
                console.error('Map initialization error:', error);
                document.getElementById('map-container').innerHTML = `
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <svg class="w-12 h-12 mx-auto mb-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-red-600 font-medium">Impossible d'initialiser la carte</p>
                            <p class="text-sm text-gray-500">Erreur technique</p>
                        </div>
                    </div>
                `;
            }
        },
        
        getRegionColor(projectCount) {
            if (projectCount > 30) return '#FF6B35'; // Orange principal
            if (projectCount > 15) return '#059669'; // Vert success
            if (projectCount > 5) return '#6B7280';  // Gris moyen
            return '#9CA3AF'; // Gris clair
        },
        
        initializeCharts() {
            // Initialize any Chart.js charts after content loads
            const chartElements = document.querySelectorAll('.chart-canvas');
            chartElements.forEach(element => {
                // Chart initialization will be done in the insights partial
            });
        }
    };
    
    // Initialize the app
    window.intelligenceApp.init();
});
</script>
@endpush