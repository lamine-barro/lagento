@extends('layouts.app')

@section('seo_title', 'Tableau de Bord Entrepreneur - LAgentO Côte d\'Ivoire')
@section('meta_description', 'Accédez à votre tableau de bord entrepreneur : consultez vos projets, interactions avec l\'assistant IA, opportunités personnalisées et analytics de votre activité entrepreneuriale en Côte d\'Ivoire.')
@section('meta_keywords', 'dashboard entrepreneur, tableau bord startup, gestion projet ci, analytics business, assistant ia personnel')
@section('title', 'Dashboard')

@section('content')
<div class="min-h-screen bg-white">
    <!-- Toggle Switch -->
    <div x-data="{ currentView: 'dashboard' }" class="sticky top-0 z-10 bg-white border-b" style="border-color: var(--gray-100);">
        <div class="flex">
            <button 
                @click="currentView = 'dashboard'"
                :class="currentView === 'dashboard' ? 'border-b-2' : ''"
                class="flex-1 py-4 px-6 text-center transition-all duration-200"
                :style="currentView === 'dashboard' ? 'color: var(--orange-primary); border-color: var(--orange-primary);' : 'color: var(--gray-700);'"
            >
                <div class="flex items-center justify-center gap-2">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                    <span class="font-medium">Analytics</span>
                </div>
            </button>
            
            <button 
                @click="currentView = 'agent'; window.location.href = '{{ route('chat') }}'"
                :class="currentView === 'agent' ? 'border-b-2' : ''"
                class="flex-1 py-4 px-6 text-center transition-all duration-200"
                :style="currentView === 'agent' ? 'color: var(--orange-primary); border-color: var(--orange-primary);' : 'color: var(--gray-700);'"
            >
                <div class="flex items-center justify-center gap-2">
                    <i data-lucide="message-square" class="w-5 h-5"></i>
                    <span class="font-medium">Agent</span>
                </div>
            </button>
        </div>
        
        <!-- Dashboard Content -->
        <div x-show="currentView === 'dashboard'" class="p-4 space-y-4">
            
            <!-- Opportunités matchées -->
            <div x-data="{ expanded: false }" class="bg-white rounded-lg border" style="border-color: var(--gray-100);">
                <button 
                    @click="expanded = !expanded"
                    class="w-full p-4 flex items-center justify-between text-left"
                >
                    <div>
                        <h3 class="font-medium" style="color: var(--gray-900);">Opportunités matchées</h3>
                        <p class="text-sm mt-1" style="color: var(--gray-500);">{{ $opportunities->count() ?? 0 }} opportunités disponibles</p>
                    </div>
                    <i data-lucide="chevron-down" 
                       class="w-5 h-5 transition-transform duration-200"
                       :class="expanded ? 'rotate-180' : ''"
                       style="color: var(--gray-500);">
                    </i>
                </button>
                
                <div x-show="expanded" x-transition class="px-4 pb-4">
                    @forelse($opportunities ?? [] as $opportunity)
                        <div class="border rounded-lg p-3 mb-3" style="border-color: var(--gray-100);">
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="font-medium text-sm" style="color: var(--gray-900);">{{ $opportunity->title }}</h4>
                                <span class="text-xs px-2 py-1 rounded-full" style="background: var(--orange-lightest); color: var(--orange-primary);">
                                    {{ $opportunity->type }}
                                </span>
                            </div>
                            <p class="text-xs mb-2" style="color: var(--gray-700);">{{ Str::limit($opportunity->description, 100) }}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs" style="color: var(--gray-500);">
                                    Échéance: {{ $opportunity->deadline }}
                                </span>
                                <button class="text-xs font-medium" style="color: var(--orange-primary);">
                                    Voir détails
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6">
                            <i data-lucide="search" class="w-8 h-8 mx-auto mb-2" style="color: var(--gray-300);"></i>
                            <p class="text-sm" style="color: var(--gray-500);">Aucune opportunité disponible pour le moment</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Conformité légale -->
            <div x-data="{ expanded: false }" class="bg-white rounded-lg border" style="border-color: var(--gray-100);">
                <button 
                    @click="expanded = !expanded"
                    class="w-full p-4 flex items-center justify-between text-left"
                >
                    <div>
                        <h3 class="font-medium" style="color: var(--gray-900);">Conformité légale</h3>
                        <p class="text-sm mt-1" style="color: var(--gray-500);">Statut de vos obligations</p>
                    </div>
                    <i data-lucide="chevron-down" 
                       class="w-5 h-5 transition-transform duration-200"
                       :class="expanded ? 'rotate-180' : ''"
                       style="color: var(--gray-500);">
                    </i>
                </button>
                
                <div x-show="expanded" x-transition class="px-4 pb-4 space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-lg" style="background: var(--success-light);">
                        <span class="text-sm font-medium" style="color: var(--gray-900);">RCCM</span>
                        <i data-lucide="check-circle" class="w-5 h-5" style="color: var(--success);"></i>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg" style="background: var(--warning-light);">
                        <span class="text-sm font-medium" style="color: var(--gray-900);">DFE</span>
                        <i data-lucide="alert-triangle" class="w-5 h-5" style="color: var(--warning);"></i>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg" style="background: var(--success-light);">
                        <span class="text-sm font-medium" style="color: var(--gray-900);">CNPS</span>
                        <i data-lucide="check-circle" class="w-5 h-5" style="color: var(--success);"></i>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg" style="background: var(--danger-light);">
                        <span class="text-sm font-medium" style="color: var(--gray-900);">Impôts</span>
                        <i data-lucide="x-circle" class="w-5 h-5" style="color: var(--danger);"></i>
                    </div>
                </div>
            </div>

            <!-- Analyse de projet -->
            <div x-data="{ expanded: false }" class="bg-white rounded-lg border" style="border-color: var(--gray-100);">
                <button 
                    @click="expanded = !expanded"
                    class="w-full p-4 flex items-center justify-between text-left"
                >
                    <div>
                        <h3 class="font-medium" style="color: var(--gray-900);">Analyse de projet</h3>
                        <p class="text-sm mt-1" style="color: var(--gray-500);">Évaluation de votre projet</p>
                    </div>
                    <i data-lucide="chevron-down" 
                       class="w-5 h-5 transition-transform duration-200"
                       :class="expanded ? 'rotate-180' : ''"
                       style="color: var(--gray-500);">
                    </i>
                </button>
                
                <div x-show="expanded" x-transition class="px-4 pb-4">
                    @if(isset($project))
                        <div class="space-y-4">
                            <!-- Score de viabilité -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium" style="color: var(--gray-900);">Score de viabilité</span>
                                    <span class="text-sm font-medium" style="color: var(--orange-primary);">{{ $project->viability_score ?? 75 }}%</span>
                                </div>
                                <div class="h-2 rounded-full" style="background: var(--gray-100);">
                                    <div class="h-2 rounded-full" style="background: var(--orange-primary); width: {{ $project->viability_score ?? 75 }}%;"></div>
                                </div>
                            </div>
                            
                            <!-- Points forts -->
                            <div>
                                <h4 class="text-sm font-medium mb-2" style="color: var(--gray-900);">Points forts</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="check" class="w-4 h-4" style="color: var(--success);"></i>
                                        <span class="text-sm" style="color: var(--gray-700);">Marché en croissance</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="check" class="w-4 h-4" style="color: var(--success);"></i>
                                        <span class="text-sm" style="color: var(--gray-700);">Équipe expérimentée</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Points d'attention -->
                            <div>
                                <h4 class="text-sm font-medium mb-2" style="color: var(--gray-900);">Points d'attention</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="alert-circle" class="w-4 h-4" style="color: var(--warning);"></i>
                                        <span class="text-sm" style="color: var(--gray-700);">Besoin de financement</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-6">
                            <i data-lucide="file-text" class="w-8 h-8 mx-auto mb-2" style="color: var(--gray-300);"></i>
                            <p class="text-sm" style="color: var(--gray-500);">Aucun projet analysé pour le moment</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistiques d'usage -->
            <div x-data="{ expanded: false }" class="bg-white rounded-lg border" style="border-color: var(--gray-100);">
                <button 
                    @click="expanded = !expanded"
                    class="w-full p-4 flex items-center justify-between text-left"
                >
                    <div>
                        <h3 class="font-medium" style="color: var(--gray-900);">Statistiques d'usage</h3>
                        <p class="text-sm mt-1" style="color: var(--gray-500);">Votre activité sur Agent O</p>
                    </div>
                    <i data-lucide="chevron-down" 
                       class="w-5 h-5 transition-transform duration-200"
                       :class="expanded ? 'rotate-180' : ''"
                       style="color: var(--gray-500);">
                    </i>
                </button>
                
                <div x-show="expanded" x-transition class="px-4 pb-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-3 rounded-lg" style="background: var(--gray-100);">
                            <div class="text-xl font-medium" style="color: var(--orange-primary);">{{ $stats['messages_sent'] ?? 0 }}</div>
                            <div class="text-xs" style="color: var(--gray-500);">Messages envoyés</div>
                        </div>
                        <div class="text-center p-3 rounded-lg" style="background: var(--gray-100);">
                            <div class="text-xl font-medium" style="color: var(--orange-primary);">{{ $stats['documents_generated'] ?? 0 }}</div>
                            <div class="text-xs" style="color: var(--gray-500);">Documents générés</div>
                        </div>
                        <div class="text-center p-3 rounded-lg" style="background: var(--gray-100);">
                            <div class="text-xl font-medium" style="color: var(--orange-primary);">{{ $stats['opportunities_matched'] ?? 0 }}</div>
                            <div class="text-xs" style="color: var(--gray-500);">Opportunités matchées</div>
                        </div>
                        <div class="text-center p-3 rounded-lg" style="background: var(--gray-100);">
                            <div class="text-xl font-medium" style="color: var(--orange-primary);">{{ $stats['time_saved'] ?? '0h' }}</div>
                            <div class="text-xs" style="color: var(--gray-500);">Temps gagné</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection