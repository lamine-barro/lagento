@extends('layouts.app')

@section('title', 'Diagnostic')

@section('content')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('diagnostic', {
        isGenerating: false,
        
        setGenerating(value) {
            this.isGenerating = value;
        }
    });
});
</script>

<div class="container max-w-4xl mx-auto section" x-data="{ 
    sectionsState: {
        profil: true,
        diagnostic: true,
        opportunites: true,
        marche: true,
        regulations: true,
        partenaires: true,
        resume: true
    },
    isGenerating: false,
    toggleSection(section) {
        this.sectionsState[section] = !this.sectionsState[section];
    },
    
    refreshDiagnostic() {
        this.$dispatch('open-diagnostic-modal');
    },
    
    runDiagnostic() {
        this.isGenerating = true;
        this.$store.diagnostic.setGenerating(true);
        
        fetch('{{ route("diagnostic.run") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            this.isGenerating = false;
            this.$store.diagnostic.setGenerating(false);
            if (data.success) {
                // Recharger la page pour afficher les nouveaux analytics
                window.location.reload();
            } else {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    // Afficher erreur avec possibilité de retry
                    let message = data.message || 'Erreur lors du diagnostic';
                    if (data.can_retry) {
                        message += '\n\nVoulez-vous réessayer ? Aucun diagnostic n\'a été décompté.';
                        if (confirm(message)) {
                            this.runDiagnostic(); // Retry automatique
                            return;
                        }
                    }
                    alert(message);
                }
            }
        })
        .catch(error => {
            this.isGenerating = false;
            this.$store.diagnostic.setGenerating(false);
            console.error('Erreur:', error);
            let message = 'Erreur de connexion lors du diagnostic.\n\nVoulez-vous réessayer ? Aucun diagnostic n\'a été décompté.';
            if (confirm(message)) {
                this.runDiagnostic(); // Retry automatique
            }
        });
    }
}" @run-diagnostic.window="runDiagnostic()">
    <!-- En-tête -->
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-primary mb-2">Diagnostic</h1>
            <p class="text-secondary">{{ isset($analytics) ? 'Dernière mise à jour: ' . ($analytics->metadata['derniere_maj'] ?? 'Non définie') : 'Vue d\'ensemble de votre activité entrepreneuriale' }}</p>
        </div>
        
        <!-- Actions -->
        <div class="flex items-center gap-2">
            <button 
                @click="refreshDiagnostic()"
                class="px-4 py-3 rounded-md border transition-colors flex items-center gap-2 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-800"
                style="border-color: var(--gray-300); color: var(--gray-700);"
                title="Rafraîchir le diagnostic"
                x-bind:disabled="$store.diagnostic.isGenerating"
                x-bind:class="$store.diagnostic.isGenerating ? 'opacity-50 cursor-not-allowed' : ''"
            >
                <i data-lucide="refresh-cw" class="w-4 h-4" x-bind:class="$store.diagnostic.isGenerating ? 'smooth-spin' : ''"></i>
                <span class="hidden sm:inline" x-bind:class="$store.diagnostic.isGenerating ? 'shimmer-text' : ''" x-text="$store.diagnostic.isGenerating ? 'Génération...' : 'Rafraîchir'"></span>
            </button>
            
            <a 
                href="{{ route('onboarding.step1') }}"
                class="px-4 py-3 rounded-md border transition-colors flex items-center gap-2 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-800"
                style="border-color: var(--gray-300); color: var(--gray-700);"
                title="Éditer les informations du projet"
            >
                <i data-lucide="edit" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Éditer projet</span>
            </a>
            
            <a 
                href="{{ route('documents.index') }}"
                class="px-4 py-3 rounded-md border transition-colors flex items-center gap-2 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-800"
                style="border-color: var(--gray-300); color: var(--gray-700);"
                title="Gérer les documents"
            >
                <i data-lucide="folder-open" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Documents</span>
            </a>
        </div>
    </div>
    
    @if(isset($analytics))
    <div class="diagnostic-cards">
        <!-- Résumé Exécutif -->
        <div class="card mb-8">
            <div class="card-header cursor-pointer" @click="toggleSection('resume')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="star" class="w-5 h-5 text-orange"></i>
                            Résumé Exécutif
                        </h3>
                        <p class="card-description">Score progression : {{ $analytics->executive_summary['score_progression'] ?? 0 }}/100</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.resume ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.resume" class="card-body">
                <div class="mb-4 p-4 rounded-lg" style="background: var(--orange-100);">
                    <h4 class="font-medium text-orange mb-2">Message Principal</h4>
                    <p class="text-sm">{{ $analytics->executive_summary['message_principal'] ?? 'Analyse en cours...' }}</p>
                </div>
                
                <div class="grid md:grid-cols-2 gap-8">
                    <div>
                        <h4 class="font-medium mb-3">3 Actions Clés</h4>
                        <div class="space-y-4">
                            @foreach(($analytics->executive_summary['trois_actions_cles'] ?? []) as $action)
                            <div class="flex items-start gap-2">
                                <div class="w-2 h-2 bg-orange rounded-full mt-2 flex-shrink-0"></div>
                                <span class="text-sm">{{ $action }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-3">Opportunité du Mois</h4>
                        <p class="text-sm p-3 rounded" style="background: var(--success-100); color: var(--success-700);">
                            {{ $analytics->executive_summary['opportunite_du_mois'] ?? 'Aucune opportunité identifiée' }}
                        </p>
                        
                        @if(isset($analytics->executive_summary['alerte_importante']))
                        <div class="mt-4">
                            <h4 class="font-medium mb-3 flex items-center gap-2 text-orange">
                                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                Alerte Importante
                            </h4>
                            <p class="text-sm p-4 rounded-lg bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300 border border-warning-200 dark:border-warning-700">
                                {{ $analytics->executive_summary['alerte_importante'] }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Profil Entrepreneur -->
        <div class="card mb-8">
            <div class="card-header cursor-pointer" @click="toggleSection('profil')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="user" class="w-5 h-5 text-orange"></i>
                            Profil Entrepreneur
                        </h3>
                        <p class="card-description">Niveau : {{ ucfirst($analytics->entrepreneur_profile['niveau_global'] ?? 'Non défini') }} | Score : {{ $analytics->entrepreneur_profile['score_potentiel'] ?? 0 }}/100</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.profil ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.profil" class="card-body">
                <div class="grid md:grid-cols-2 gap-8">
                    <div>
                        <h4 class="font-medium mb-4 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="zap" class="w-4 h-4 text-orange"></i>
                            Forces Identifiées
                        </h4>
                        <div class="space-y-4">
                            @foreach(($analytics->entrepreneur_profile['forces'] ?? []) as $force)
                            <div class="p-4 rounded-lg bg-gray-50/10 dark:bg-gray-800/5 border border-gray-100/20 dark:border-gray-700/10">
                                <h5 class="font-medium text-orange mb-2">{{ $force['domaine'] ?? 'N/A' }}</h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $force['description'] ?? 'N/A' }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-4 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="trending-up" class="w-4 h-4 text-blue-500"></i>
                            Axes de Progression
                        </h4>
                        <div class="space-y-4">
                            @foreach(($analytics->entrepreneur_profile['axes_progression'] ?? []) as $axe)
                            <div class="p-4 rounded-lg bg-gray-50/10 dark:bg-gray-800/5 border border-gray-100/20 dark:border-gray-700/10">
                                <div class="flex items-start justify-between mb-2">
                                    <h5 class="font-medium text-gray-900 dark:text-gray-100">{{ $axe['domaine'] ?? 'N/A' }}</h5>
                                    <span class="text-xs badge badge-{{ ($axe['impact'] ?? null) === 'immédiat' ? 'orange' : (($axe['impact'] ?? null) === 'court_terme' ? 'blue' : 'gray') }}">{{ $axe['impact'] ?? 'N/A' }}</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $axe['action_suggeree'] ?? 'N/A' }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Diagnostic Projet -->
        <div class="card mb-8">
            <div class="card-header cursor-pointer" @click="toggleSection('diagnostic')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="activity" class="w-5 h-5 text-orange"></i>
                            Diagnostic Projet
                        </h3>
                        <p class="card-description">Santé : {{ $analytics->project_diagnostic['score_sante'] ?? 0 }}/100 | Maturité : {{ ucfirst($analytics->project_diagnostic['niveau_maturite'] ?? 'Non défini') }}</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.diagnostic ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.diagnostic" class="card-body">
                <div class="grid md:grid-cols-2 gap-8">
                    <div>
                        <h4 class="font-medium mb-4 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="bar-chart-3" class="w-4 h-4 text-green-500"></i>
                            Indicateurs Clés
                        </h4>
                        <div class="space-y-4">
                            @if(isset($analytics->project_diagnostic['indicateurs_cles']))
                            @foreach($analytics->project_diagnostic['indicateurs_cles'] as $domaine => $indicateur)
                            <div class="p-4 rounded-lg bg-gray-50/10 dark:bg-gray-800/5 border border-gray-100/20 dark:border-gray-700/10">
                                <div class="flex items-center justify-between mb-2">
                                    <h5 class="font-medium capitalize text-gray-900 dark:text-gray-100">{{ $domaine }}</h5>
                                    <span class="badge badge-{{ ($indicateur['statut'] ?? null) === 'ok' ? 'success' : (($indicateur['statut'] ?? null) === 'en_cours' ? 'warning' : 'gray') }}">{{ $indicateur['statut'] ?? 'N/A' }}</span>
                                </div>
                                @if(isset($indicateur['urgence']))
                                <p class="text-sm text-gray-600 dark:text-gray-400">Urgence : {{ $indicateur['urgence'] }}</p>
                                @endif
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-4 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="arrow-right" class="w-4 h-4 text-orange"></i>
                            Prochaines Étapes
                        </h4>
                        <div class="space-y-4">
                            @foreach(($analytics->project_diagnostic['prochaines_etapes'] ?? []) as $etape)
                            <div class="p-4 rounded-lg bg-gray-50/10 dark:bg-gray-800/5 border border-gray-100/20 dark:border-gray-700/10">
                                <div class="flex items-start gap-3">
                                    <span class="bg-orange text-white text-xs rounded-full w-6 h-6 flex items-center justify-center flex-shrink-0 font-medium">{{ $etape['priorite'] ?? '?' }}</span>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">{{ $etape['action'] ?? 'N/A' }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Délai : {{ $etape['delai'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Opportunités Matchées -->
        <div class="card mb-8">
            <div class="card-header cursor-pointer" @click="toggleSection('opportunites')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="target" class="w-5 h-5 text-orange"></i>
                            Opportunités Matchées
                        </h3>
                        <p class="card-description">{{ $analytics->matched_opportunities['nombre_total'] ?? 0 }} opportunités identifiées</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.opportunites ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.opportunites" class="card-body">
                <div class="space-y-5">
                    @foreach(($analytics->matched_opportunities['top_opportunites'] ?? []) as $opportunite)
                    <div class="p-5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:shadow-sm transition-shadow">
                        <div class="flex items-start justify-between mb-3">
                            <h4 class="font-semibold text-orange text-base">{{ $opportunite['titre'] ?? 'N/A' }}</h4>
                            <span class="badge badge-{{ ($opportunite['urgence'] ?? null) === 'candidater_avant_7j' ? 'orange' : 'blue' }}">{{ $opportunite['urgence'] ?? 'N/A' }}</span>
                        </div>
                        <div class="mb-3">
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $opportunite['institution'] ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Compatibilité : {{ $opportunite['score_compatibilite'] ?? 0 }}%</p>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">{{ $opportunite['pourquoi_vous'] ?? 'N/A' }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-orange">{{ $opportunite['montant_ou_valeur'] ?? 'N/A' }}</span>
                            @if(isset($opportunite['lien']))
                            <a href="{{ $opportunite['lien'] }}" class="btn btn-sm btn-secondary">Candidater</a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Insights Marché -->
        <div class="card mb-8">
            <div class="card-header cursor-pointer" @click="toggleSection('marche')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="trending-up" class="w-5 h-5 text-orange"></i>
                            Insights Marché
                        </h3>
                        <p class="card-description">Position : {{ $analytics->market_insights['position_concurrentielle']['votre_place'] ?? 'Non définie' }}</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.marche ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.marche" class="card-body">
                <div class="grid md:grid-cols-2 gap-8">
                    <div>
                        <h4 class="font-medium mb-4 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="dollar-sign" class="w-4 h-4 text-green-500"></i>
                            Taille du Marché
                        </h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Local :</span>
                                <span class="text-sm font-medium text-orange ml-4">{{ $analytics->market_insights['taille_marche']['local'] ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Potentiel :</span>
                                <span class="text-sm font-medium text-orange ml-4">{{ $analytics->market_insights['taille_marche']['potentiel'] ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Croissance :</span>
                                <span class="text-sm font-medium text-success ml-4">{{ $analytics->market_insights['taille_marche']['croissance'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-4 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="map-pin" class="w-4 h-4 text-purple-500"></i>
                            Zones d'Opportunités
                        </h4>
                        <div class="space-y-4">
                            @foreach(($analytics->market_insights['zones_opportunites'] ?? []) as $zone)
                            <div class="p-4 rounded-lg bg-gray-50/10 dark:bg-gray-800/5 border border-gray-100/20 dark:border-gray-700/10">
                                <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-2">{{ $zone['region'] ?? 'N/A' }}</h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $zone['raison'] ?? 'N/A' }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Réglementations -->
        <div class="card mb-8">
            <div class="card-header cursor-pointer" @click="toggleSection('regulations')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-5 h-5 text-orange"></i>
                            Conformité Réglementaire
                        </h3>
                        <p class="card-description">Statut : {{ ucfirst($analytics->regulations['conformite_globale'] ?? 'Non défini') }}</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.regulations ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.regulations" class="card-body">
                <div class="grid md:grid-cols-2 gap-8">
                    <div>
                        <h4 class="font-medium mb-4 flex items-center gap-2 text-orange">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                            Urgent
                        </h4>
                        <div class="space-y-4">
                            @foreach(($analytics->regulations['urgent'] ?? []) as $urgent)
                            <div class="p-4 rounded-lg border-l-4 border-orange bg-orange-50/10 dark:bg-orange-900/5">
                                <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-2">{{ $urgent['obligation'] ?? 'N/A' }}</h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Deadline : {{ $urgent['deadline'] ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">Coût : {{ $urgent['cout'] ?? 'N/A' }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-4 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="calendar" class="w-4 h-4 text-blue-500"></i>
                            À Prévoir
                        </h4>
                        <div class="space-y-4">
                            @foreach(($analytics->regulations['a_prevoir'] ?? []) as $prevoir)
                            <div class="p-4 rounded-lg bg-gray-50/10 dark:bg-gray-800/5 border border-gray-100/20 dark:border-gray-700/10">
                                <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-2">{{ $prevoir['obligation'] ?? 'N/A' }}</h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Échéance : {{ $prevoir['echeance'] ?? 'N/A' }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Partenaires Suggérés -->
        <div class="card mb-8">
            <div class="card-header cursor-pointer" @click="toggleSection('partenaires')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="users" class="w-5 h-5 text-orange"></i>
                            Partenaires Suggérés
                        </h3>
                        <p class="card-description">{{ $analytics->suggested_partners['nombre_matches'] ?? 0 }} partenaires potentiels</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.partenaires ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.partenaires" class="card-body">
                <div class="space-y-5">
                    @foreach(($analytics->suggested_partners['top_partenaires'] ?? []) as $partenaire)
                    <div class="p-5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:shadow-sm transition-shadow">
                        <div class="flex items-start justify-between mb-3">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-base">{{ $partenaire['nom_projet'] ?? 'N/A' }}</h4>
                            <span class="badge badge-blue">{{ $partenaire['score_pertinence'] ?? 0 }}% match</span>
                        </div>
                        <div class="mb-3">
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $partenaire['secteur'] ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $partenaire['localisation'] ?? 'N/A' }}</p>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">{{ $partenaire['proposition_collaboration'] ?? 'N/A' }}</p>
                        <span class="text-xs badge badge-{{ ($partenaire['type_synergie'] ?? null) === 'strategique' ? 'orange' : 'gray' }}">{{ $partenaire['type_synergie'] ?? 'N/A' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-body text-center py-12" x-show="!isGenerating">
            <i data-lucide="lightbulb" class="w-16 h-16 mx-auto mb-4 text-gray-300"></i>
            <h3 class="text-lg font-medium text-primary mb-2">Aucun diagnostic disponible</h3>
            <p class="text-muted">Lancez votre premier diagnostic pour obtenir une analyse complète de votre profil entrepreneurial.</p>
        </div>
        
        <!-- État de génération avec shimmer -->
        <div class="card-body py-12" x-show="isGenerating" style="display: none;">
            <div class="text-center mb-8">
                <div class="smooth-spin w-8 h-8 border-2 border-orange border-t-transparent rounded-full mx-auto mb-4"></div>
                <h3 class="text-lg font-medium text-primary mb-2 shimmer-text">Analytics en cours de génération</h3>
                <p class="text-muted shimmer-text">L'IA analyse vos données entrepreneuriales...</p>
            </div>
            
            <!-- Shimmer cards preview -->
            <div class="space-y-4">
                <div class="skeleton skeleton-card"></div>
                <div class="skeleton skeleton-card"></div>
                <div class="skeleton skeleton-card"></div>
                
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="skeleton skeleton-card"></div>
                    <div class="skeleton skeleton-card"></div>
                </div>
                
                <div class="space-y-2">
                    <div class="skeleton skeleton-title"></div>
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text" style="width: 80%;"></div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Diagnostic Confirmation Modal -->
<div x-data="{ open: false }" 
     @open-diagnostic-modal.window="open = true"
     x-show="open" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center" 
     style="display: none;">
    
    <!-- Backdrop with blur -->
    <div class="fixed inset-0 backdrop-blur-sm" style="background: rgba(0, 0, 0, 0.5);" @click="open = false"></div>
    
    <!-- Modal -->
    <div @click.stop 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative w-full max-w-md mx-4 bg-white rounded-lg shadow-2xl">
        
        <div class="p-6">
            <!-- Header -->
            <h3 class="text-lg font-semibold mb-4" style="color: var(--gray-900);">
                Lancer un diagnostic
            </h3>
            
            <!-- Content -->
            <div class="mb-6">
                <p class="text-sm mb-4" style="color: var(--gray-700);">
                    Vous avez <strong>{{ auth()->user()->getRemainingDiagnostics() }} diagnostic(s) restant(s)</strong> ce mois-ci.
                </p>
                
                @if(auth()->user()->canRunDiagnostic())
                    <p class="text-sm" style="color: var(--gray-700);">
                        Voulez-vous lancer l'analyse de votre profil entrepreneurial ?
                    </p>
                @else
                    <div class="p-3 rounded-lg" style="background: var(--warning-50); border: 1px solid var(--warning-200);">
                        <p class="text-sm font-medium" style="color: var(--warning-700);">
                            ⚠️ Vous avez atteint la limite mensuelle de 50 diagnostics
                        </p>
                        <p class="text-xs mt-1" style="color: var(--warning-600);">
                            Prochain reset : {{ now()->addMonth()->startOfMonth()->format('d/m/Y') }}
                        </p>
                    </div>
                @endif
            </div>
            
            <!-- Actions -->
            <div class="flex gap-3">
                <button 
                    type="button"
                    @click="open = false"
                    class="btn btn-ghost flex-1"
                >
                    Annuler
                </button>
                @if(auth()->user()->canRunDiagnostic())
                    <button 
                        type="button"
                        @click="$dispatch('run-diagnostic'); open = false"
                        class="btn btn-primary flex-1"
                        x-bind:disabled="$store.diagnostic.isGenerating"
                        x-bind:class="$store.diagnostic.isGenerating ? 'opacity-75 cursor-not-allowed' : ''"
                    >
                        <span x-show="!$store.diagnostic.isGenerating">Lancer le diagnostic</span>
                        <span x-show="$store.diagnostic.isGenerating" class="flex items-center gap-2">
                            <div class="smooth-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"></div>
                            <span class="shimmer-text">Génération en cours...</span>
                        </span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection