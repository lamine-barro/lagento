@extends('layouts.app')

@section('title', 'Intelligence Économique - République de Côte d\'Ivoire')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 to-green-50">
    <!-- Header Présidentiel -->
    <div class="bg-white border-b-2 border-orange-500 shadow-sm">
        <div class="container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center space-x-4">
                    <div class="h-12 w-12 bg-gradient-to-br from-orange-600 to-green-600 rounded-lg flex items-center justify-center">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Intelligence Économique</h1>
                        <p class="text-sm text-gray-600">Observatoire National de l'Entrepreneuriat</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 60 40'%3E%3Crect width='20' height='40' fill='%23ff8c00'/%3E%3Crect x='20' width='20' height='40' fill='%23ffffff'/%3E%3Crect x='40' width='20' height='40' fill='%2300b04f'/%3E%3C/svg%3E" alt="Drapeau Côte d'Ivoire" class="h-6 w-9 rounded border">
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">République de Côte d'Ivoire</p>
                        <p class="text-xs text-gray-600">Union - Discipline - Travail</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Pills -->
    <div class="container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-2 mb-8">
            <nav class="flex space-x-2" x-data="{ activeTab: 'insights' }">
                <button @click="activeTab = 'insights'" :class="activeTab === 'insights' ? 'bg-gradient-to-r from-orange-500 to-green-500 text-white' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'" class="flex items-center px-6 py-3 rounded-lg font-medium transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Insights Territoire National
                </button>
                <button @click="activeTab = 'directory'" :class="activeTab === 'directory' ? 'bg-gradient-to-r from-orange-500 to-green-500 text-white' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'" class="flex items-center px-6 py-3 rounded-lg font-medium transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Annuaire des Projets
                </button>
                <button @click="activeTab = 'diagnostics'" :class="activeTab === 'diagnostics' ? 'bg-gradient-to-r from-orange-500 to-green-500 text-white' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'" class="flex items-center px-6 py-3 rounded-lg font-medium transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Diagnostics Agrégés
                </button>
            </nav>
        </div>

        <!-- Content Areas -->
        <div x-show="activeTab === 'insights'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            <div id="insights-content">
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
                    <p class="mt-4 text-gray-600">Chargement des insights...</p>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'directory'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            <div id="directory-content">
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
                    <p class="mt-4 text-gray-600">Chargement de l'annuaire...</p>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'diagnostics'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            <div id="diagnostics-content">
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
                    <p class="mt-4 text-gray-600">Chargement des diagnostics...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('intelligencePage', () => ({
        activeTab: 'insights',
        
        init() {
            this.loadInsights();
            
            this.$watch('activeTab', (tab) => {
                switch(tab) {
                    case 'insights':
                        this.loadInsights();
                        break;
                    case 'directory':
                        this.loadDirectory();
                        break;
                    case 'diagnostics':
                        this.loadDiagnostics();
                        break;
                }
            });
        },

        loadInsights() {
            fetch('/intelligence/insights')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('insights-content').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('insights-content').innerHTML = '<div class="text-center py-8 text-red-600">Erreur lors du chargement</div>';
                });
        },

        loadDirectory() {
            fetch('/intelligence/directory')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('directory-content').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('directory-content').innerHTML = '<div class="text-center py-8 text-red-600">Erreur lors du chargement</div>';
                });
        },

        loadDiagnostics() {
            document.getElementById('diagnostics-content').innerHTML = `
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">Diagnostics Agrégés</h3>
                        <p class="mt-2 text-gray-600">Analyse en cours des diagnostics entrepreneuriaux sur le territoire national.</p>
                        <p class="mt-4 text-sm text-orange-600 font-medium">Module en développement - Disponible prochainement</p>
                    </div>
                </div>
            `;
        }
    }));
});
</script>
@endpush