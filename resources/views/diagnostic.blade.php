@extends('layouts.app')

@section('title', 'Diagnostic entreprise')
@section('page_title', 'Diagnostic entreprise gratuit - Analysez votre startup avec l\'IA | Agento')
@section('seo_title', 'Diagnostic entreprise gratuit - Analysez votre startup avec l\'IA | Agento')
@section('meta_description', 'Obtenez un diagnostic complet et gratuit de votre entreprise avec l\'intelligence artificielle. Analyse des forces, faiblesses, opportunités de financement et conseils personnalisés pour entrepreneurs ivoiriens.')
@section('meta_keywords', 'diagnostic entreprise gratuit, analyse startup, conseil business CI, diagnostic IA, évaluation entreprise côte ivoire, audit business abidjan')
@section('canonical_url', route('diagnostic'))
@section('og_title', 'Diagnostic entreprise IA gratuit - Agento Côte d\'Ivoire')
@section('og_description', 'Diagnostic IA complet de votre entreprise : forces, faiblesses, opportunités de financement et plan d\'action personnalisé. Gratuit pour entrepreneurs ivoiriens.')
@section('schema_org')
@verbatim
{
    "@context": "https://schema.org",
    "@type": "Service",
    "name": "Diagnostic Entreprise IA",
    "description": "Service de diagnostic d'entreprise par intelligence artificielle pour entrepreneurs ivoiriens",
    "provider": {
        "@type": "Organization",
        "name": "Agento",
        "url": "@endverbatim{{ url('/') }}@verbatim"
    },
    "areaServed": {
        "@type": "Country",
        "name": "Côte d'Ivoire"
    },
    "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "XOF"
    }
}
@endverbatim
@endsection

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

<div class="container max-w-4xl mx-auto section px-4" x-data="{ 
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
    progressStep: 0,
    progressSteps: [
        'Collecte des informations du profil',
        'Analyse du marché et opportunités', 
        'Évaluation des conformités',
        'Génération du rapport personnalisé'
    ],
    
    toggleSection(section) {
        this.sectionsState[section] = !this.sectionsState[section];
    },
    
    refreshDiagnostic() {
        this.$dispatch('open-diagnostic-modal');
    },
    
    simulateProgress() {
        this.progressStep = 0;
        const interval = setInterval(() => {
            this.progressStep++;
            if (this.progressStep >= this.progressSteps.length) {
                clearInterval(interval);
            }
        }, 3000); // Change l'étape toutes les 3 secondes
        
        return interval;
    },
    
    runDiagnostic() {
        this.isGenerating = true;
        this.$store.diagnostic.setGenerating(true);
        
        // Démarrer l'animation de progression
        const progressInterval = this.simulateProgress();
        
        fetch('{{ route("diagnostic.run") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(progressInterval);
            this.isGenerating = false;
            this.$store.diagnostic.setGenerating(false);
            this.progressStep = 0;
            
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
            clearInterval(progressInterval);
            this.isGenerating = false;
            this.$store.diagnostic.setGenerating(false);
            this.progressStep = 0;
            console.error('Erreur:', error);
            let message = 'Erreur de connexion lors du diagnostic.\n\nVoulez-vous réessayer ? Aucun diagnostic n\'a été décompté.';
            if (confirm(message)) {
                this.runDiagnostic(); // Retry automatique
            }
        });
    }
}" @run-diagnostic.window="runDiagnostic()">
    <!-- En-tête compact -->
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-primary mb-1">Diagnostic</h1>
            <p class="text-secondary text-sm">{{ isset($analytics) ? 'MAJ: ' . ($analytics->updated_at ? $analytics->updated_at->format('d/m/Y H:i') : 'N/A') : 'Vue d\'ensemble entrepreneuriale' }}</p>
        </div>
        
        <!-- Actions -->
        <div class="flex items-center gap-3">
            <button 
                @click="refreshDiagnostic()"
                class="p-3 rounded-md border transition-colors flex items-center justify-center text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-800"
                style="border-color: var(--gray-300); color: var(--gray-700);"
                title="Rafraîchir le diagnostic"
                x-bind:disabled="$store.diagnostic.isGenerating"
                x-bind:class="$store.diagnostic.isGenerating ? 'opacity-50 cursor-not-allowed' : ''"
            >
                <i data-lucide="refresh-cw" class="w-4 h-4" x-bind:class="$store.diagnostic.isGenerating ? 'smooth-spin' : ''"></i>
            </button>
            
            <a 
                href="{{ route('onboarding.step1') }}"
                class="p-3 rounded-md border transition-colors flex items-center justify-center text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-800"
                style="border-color: var(--gray-300); color: var(--gray-700);"
                title="Éditer les informations du projet"
            >
                <i data-lucide="edit" class="w-4 h-4"></i>
            </a>
            
            <a 
                href="{{ route('documents.index') }}"
                class="p-3 rounded-md border transition-colors flex items-center justify-center text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-800"
                style="border-color: var(--gray-300); color: var(--gray-700);"
                title="Gérer les documents"
            >
                <i data-lucide="folder-open" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
    
    <!-- Loading State pour régénération de diagnostic existant -->
    <div x-show="isGenerating && @json(isset($analytics))" x-transition class="mb-4" style="display: none;">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-center py-8">
                    <div class="text-center">
                        <div class="smooth-spin w-8 h-8 border-2 rounded-full mx-auto mb-4" style="border-color: var(--orange-primary); border-top-color: transparent;"></div>
                        <h3 class="text-lg font-medium mb-2 shimmer-text" style="color: var(--gray-900);">Régénération du diagnostic en cours</h3>
                        <p class="shimmer-text" style="color: var(--gray-600);">L'IA analyse vos nouvelles données entrepreneuriales...</p>
                        
                        <!-- Progress indicator avec étapes -->
                        <div class="mt-6 space-y-3">
                            <template x-for="(step, index) in progressSteps" :key="index">
                                <div class="flex items-center justify-center gap-2 text-sm">
                                    <div class="w-2 h-2 rounded-full animate-pulse" 
                                         :style="index <= progressStep ? 'background-color: var(--orange-primary)' : 'background-color: var(--gray-300)'"></div>
                                    <span x-text="step" 
                                          :class="index <= progressStep ? 'shimmer-text' : ''"
                                          :style="'color: var(--gray-' + (index <= progressStep ? '700' : '500') + ')'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @if(isset($analytics))
    <div class="diagnostic-cards space-y-4" x-show="!isGenerating" x-transition>
        <!-- Résumé Exécutif -->
        <div class="card">
            <div class="card-header cursor-pointer" @click="toggleSection('resume')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="file-text" class="w-5 h-5 text-orange"></i>
                            Résumé exécutif
                        </h3>
                        <p class="card-description">Score : {{ $analytics->executive_summary['score_progression'] ?? 0 }}/100</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors" style="z-index: 0;">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.resume ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-show="sectionsState.resume" x-transition class="card-body">
                <div class="mb-3 p-3 rounded-lg message-principal">
                    <h4 class="font-medium text-orange mb-1">Message principal</h4>
                    <p class="text-sm">{{ $analytics->executive_summary['message_principal'] ?? 'Analyse en cours...' }}</p>
                </div>
                
                <div class="grid lg:grid-cols-2 gap-4 mt-4">
                    <div>
                        <h4 class="font-medium mb-3">3 actions clés</h4>
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
                        <h4 class="font-medium mb-3">Opportunité du mois</h4>
                        @php
                            $opportunite = $analytics->opportunite_du_mois ?? $analytics->executive_summary['opportunite_du_mois'] ?? null;
                            
                            // Si c'est du JSON, le parser
                            if (is_string($opportunite) && str_starts_with($opportunite, '{')) {
                                try {
                                    $opportuniteData = json_decode($opportunite, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($opportuniteData)) {
                                        $opportunite = $opportuniteData;
                                    }
                                } catch (Exception $e) {
                                    // Garder la string originale si erreur
                                }
                            }
                        @endphp
                        
                        @if(is_array($opportunite))
                            <div class="text-sm p-3 rounded" style="background: var(--success-100); color: var(--success-700);">
                                @if(!empty($opportunite['titre']) && $opportunite['titre'] !== 'non disponible')
                                    <div class="font-medium mb-2" style="color: var(--gray-900);">{{ $opportunite['titre'] }}</div>
                                @endif
                                
                                {{-- Description ou montant --}}
                                @if(!empty($opportunite['description']) && $opportunite['description'] !== 'non disponible')
                                    <div class="mb-3">{{ $opportunite['description'] }}</div>
                                @elseif(!empty($opportunite['montant']) && $opportunite['montant'] !== 'non disponible')
                                    <div class="mb-3">{{ $opportunite['montant'] }}</div>
                                @endif
                                
                                {{-- Métadonnées --}}
                                <div class="flex flex-wrap gap-4 text-xs mb-3" style="color: var(--gray-600);">
                                    @if(!empty($opportunite['institution']) && $opportunite['institution'] !== 'non disponible')
                                        <div class="flex items-center gap-1">
                                            <i data-lucide="building-2" class="w-3 h-3"></i>
                                            <span>{{ $opportunite['institution'] }}</span>
                                        </div>
                                    @endif
                                    
                                    @if(!empty($opportunite['type']) && $opportunite['type'] !== 'non disponible')
                                        <div class="flex items-center gap-1">
                                            <i data-lucide="tag" class="w-3 h-3"></i>
                                            <span>{{ $opportunite['type'] }}</span>
                                        </div>
                                    @endif
                                    
                                    @if(!empty($opportunite['deadline']) && $opportunite['deadline'] !== 'non disponible')
                                        <div class="flex items-center gap-1">
                                            <i data-lucide="calendar" class="w-3 h-3"></i>
                                            <span>{{ $opportunite['deadline'] }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                {{-- Lien --}}
                                @if(!empty($opportunite['lien']) && $opportunite['lien'] !== 'non disponible')
                                    @php
                                        $lien = $opportunite['lien'];
                                        // Ajouter https:// si pas de protocole
                                        if (!str_starts_with($lien, 'http')) {
                                            $lien = 'https://' . ltrim($lien, '/');
                                        }
                                    @endphp
                                    <div class="mt-2">
                                        <a href="{{ $lien }}" target="_blank" class="text-orange hover:underline text-xs font-medium">
                                            Voir détails →
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @elseif($opportunite && $opportunite !== 'non disponible')
                            <p class="text-sm p-3 rounded" style="background: var(--success-100); color: var(--success-700);">
                                {{ $opportunite }}
                            </p>
                        @else
                            <p class="text-sm p-3 rounded" style="background: var(--gray-50); color: var(--gray-600);">
                                Aucune opportunité identifiée pour ce mois
                            </p>
                        @endif
                        
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
        <div class="card mb-4">
            <div class="card-header cursor-pointer" @click="toggleSection('profil')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="user" class="w-5 h-5 text-orange"></i>
                            Profil entrepreneur
                        </h3>
                        <p class="card-description">Score : {{ $analytics->entrepreneur_profile['score_potentiel'] ?? 0 }}/100 | Niveau : {{ ucfirst($analytics->entrepreneur_profile['niveau_global'] ?? 'Non défini') }}</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors" style="z-index: 0;">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.profil ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-show="sectionsState.profil" x-transition class="card-body">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium mb-3 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="zap" class="w-4 h-4 text-orange"></i>
                            Forces identifiées
                        </h4>
                        <div class="space-y-3">
                            @foreach(($analytics->entrepreneur_profile['forces'] ?? []) as $force)
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <h5 class="font-medium text-orange mb-1">{{ $force['domaine'] ?? 'N/A' }}</h5>
                                <p class="text-sm text-gray-600">{{ $force['description'] ?? 'N/A' }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-3 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="trending-up" class="w-4 h-4 text-blue-500"></i>
                            Axes de progression
                        </h4>
                        <div class="space-y-3">
                            @foreach(($analytics->entrepreneur_profile['axes_progression'] ?? []) as $axe)
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <div class="flex items-start justify-between mb-1">
                                    <h5 class="font-medium text-gray-900">{{ $axe['domaine'] ?? 'N/A' }}</h5>
                                    <span class="text-xs badge badge-{{ ($axe['impact'] ?? null) === 'immédiat' ? 'orange' : (($axe['impact'] ?? null) === 'court_terme' ? 'orange' : 'gray') }}">{{ $axe['impact'] ?? 'N/A' }}</span>
                                </div>
                                <p class="text-sm text-gray-600">{{ $axe['action_suggeree'] ?? 'N/A' }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Diagnostic Projet -->
        <div class="card mb-4">
            <div class="card-header cursor-pointer" @click="toggleSection('diagnostic')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="chart-bar" class="w-5 h-5 text-orange"></i>
                            Maturité du projet
                        </h3>
                        <p class="card-description">Santé : {{ $analytics->project_diagnostic['score_sante'] ?? 0 }}/100 | Maturité : {{ ucfirst($analytics->project_diagnostic['niveau_maturite'] ?? 'Non défini') }}</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors" style="z-index: 0;">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.diagnostic ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-show="sectionsState.diagnostic" x-transition class="card-body">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium mb-3 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="bar-chart-3" class="w-4 h-4 text-green-500"></i>
                            Indicateurs clés
                        </h4>
                        <div class="space-y-3">
                            @if(isset($analytics->project_diagnostic['indicateurs_cles']))
                            @foreach($analytics->project_diagnostic['indicateurs_cles'] as $domaine => $indicateur)
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <div class="flex items-center justify-between mb-1">
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
                        <h4 class="font-medium mb-3 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="arrow-right" class="w-4 h-4 text-orange"></i>
                            Prochaines étapes
                        </h4>
                        <div class="space-y-3">
                            @foreach(($analytics->project_diagnostic['prochaines_etapes'] ?? []) as $etape)
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <div class="flex items-start gap-2">
                                    <span class="bg-orange text-white text-xs rounded-full w-5 h-5 flex items-center justify-center flex-shrink-0 font-medium">{{ $etape['priorite'] ?? '?' }}</span>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">{{ $etape['action'] ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">Délai : {{ $etape['delai'] ?? 'N/A' }}</p>
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
        <div class="card mb-4">
            <div class="card-header cursor-pointer" @click="toggleSection('opportunites')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-5 h-5 text-orange"></i>
                            Opportunités matchées
                        </h3>
                        <p class="card-description">{{ $analytics->matched_opportunities['nombre_total'] ?? 0 }} opportunités identifiées</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors" style="z-index: 0;">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.opportunites ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-show="sectionsState.opportunites" x-transition class="card-body">
                <div class="space-y-4">
                    @foreach(($analytics->matched_opportunities['top_opportunites'] ?? []) as $opportunite)
                    <div class="p-4 rounded-lg border border-gray-200 bg-white opportunity-card">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-semibold text-orange text-base">{{ $opportunite['titre'] ?? 'N/A' }}</h4>
                            <span class="badge badge-{{ ($opportunite['urgence'] ?? null) === 'candidater_avant_7j' ? 'orange' : 'blue' }}">{{ $opportunite['urgence'] ?? 'N/A' }}</span>
                        </div>
                        <div class="mb-2">
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $opportunite['institution'] ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Compatibilité : {{ $opportunite['score_compatibilite'] ?? 0 }}%</p>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">{{ $opportunite['pourquoi_vous'] ?? 'N/A' }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-orange">{{ $opportunite['montant_ou_valeur'] ?? 'N/A' }}</span>
                            @if(isset($opportunite['lien']) && !empty($opportunite['lien']) && $opportunite['lien'] !== 'non disponible' && $opportunite['lien'] !== 'N/A')
                                @php
                                    $lien = $opportunite['lien'];
                                    // Ajouter https:// si pas de protocole
                                    if (!str_starts_with($lien, 'http') && !str_starts_with($lien, 'mailto:')) {
                                        $lien = 'https://' . ltrim($lien, '/');
                                    }
                                @endphp
                                <a href="{{ $lien }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-secondary">Candidater</a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Insights Marché -->
        <div class="card mb-4">
            <div class="card-header cursor-pointer" @click="toggleSection('marche')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="trending-up" class="w-5 h-5 text-orange"></i>
                            Marché
                        </h3>
                        <p class="card-description">{{ $analytics->market_insights['position_concurrentielle']['votre_place'] ?? 'Non définie' }}</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors" style="z-index: 0;">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.marche ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-show="sectionsState.marche" x-transition class="card-body">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium mb-3 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="dollar-sign" class="w-4 h-4 text-green-500"></i>
                            Taille du marché
                        </h4>
                        <div class="space-y-3">
                            <!-- Marché Local -->
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Marché Local</h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $analytics->market_insights['taille_marche']['local'] ?? 'N/A' }}
                                </p>
                            </div>
                            
                            <!-- Potentiel -->
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Potentiel de Croissance</h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $analytics->market_insights['taille_marche']['potentiel'] ?? 'N/A' }}
                                </p>
                            </div>
                            
                            <!-- Croissance -->
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Taux de Croissance</h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $analytics->market_insights['taille_marche']['croissance'] ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-3 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="map-pin" class="w-4 h-4 text-purple-500"></i>
                            Zones d'opportunités
                        </h4>
                        <div class="space-y-3">
                            @foreach(($analytics->market_insights['zones_opportunites'] ?? []) as $zone)
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-1">{{ $zone['region'] ?? 'N/A' }}</h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $zone['raison'] ?? 'N/A' }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Réglementations -->
        <div class="card mb-4">
            <div class="card-header cursor-pointer" @click="toggleSection('regulations')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="scale" class="w-5 h-5 text-orange"></i>
                            Conformité réglementaire
                        </h3>
                        <p class="card-description">Statut : {{ ucfirst($analytics->regulations['conformite_globale'] ?? 'Non défini') }}</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors" style="z-index: 0;">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.regulations ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-show="sectionsState.regulations" x-transition class="card-body">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium mb-3 flex items-center gap-2 text-orange">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                            Urgent
                        </h4>
                        <div class="space-y-3">
                            @foreach(($analytics->regulations['urgent'] ?? []) as $urgent)
                            <div class="p-3 rounded-lg border-l-3 border-orange bg-orange-50/10 dark:bg-orange-900/5">
                                <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-1">{{ $urgent['obligation'] ?? 'N/A' }}</h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Deadline : {{ $urgent['deadline'] ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">Coût : {{ $urgent['cout'] ?? 'N/A' }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-3 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="calendar" class="w-4 h-4 text-blue-500"></i>
                            À prévoir
                        </h4>
                        <div class="space-y-3">
                            @foreach(($analytics->regulations['a_prevoir'] ?? []) as $prevoir)
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-1">{{ $prevoir['obligation'] ?? 'N/A' }}</h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Échéance : {{ $prevoir['echeance'] ?? 'N/A' }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Partenaires Suggérés -->
        <div class="card mb-4">
            <div class="card-header cursor-pointer" @click="toggleSection('partenaires')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="handshake" class="w-5 h-5 text-orange"></i>
                            Partenaires suggérés
                        </h3>
                        <p class="card-description">{{ $analytics->suggested_partners['nombre_matches'] ?? 0 }} partenaires potentiels</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors" style="z-index: 0;">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.partenaires ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-show="sectionsState.partenaires" x-transition class="card-body">
                <div class="space-y-4">
                    @foreach(($analytics->suggested_partners['top_partenaires'] ?? []) as $partenaire)
                    <div class="p-4 rounded-lg border border-gray-200 bg-white partner-card">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-base">{{ $partenaire['nom_projet'] ?? 'N/A' }}</h4>
                            <span class="badge badge-blue">{{ $partenaire['score_pertinence'] ?? 0 }}% match</span>
                        </div>
                        <div class="mb-2">
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $partenaire['secteur'] ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $partenaire['localisation'] ?? 'N/A' }}</p>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">{{ $partenaire['proposition_collaboration'] ?? 'N/A' }}</p>
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
        
        <!-- État de génération avec shimmer amélioré -->
        <div class="card-body py-12" x-show="isGenerating" style="display: none;">
            <div class="text-center mb-8">
                <div class="smooth-spin w-8 h-8 border-2 rounded-full mx-auto mb-4" style="border-color: var(--orange-primary); border-top-color: transparent;"></div>
                <h3 class="text-lg font-medium mb-2 shimmer-text" style="color: var(--gray-900);">Diagnostic en cours de génération</h3>
                <p class="shimmer-text" style="color: var(--gray-600);">L'IA analyse vos données entrepreneuriales pour la première fois...</p>
                
                <!-- Progress indicator avec étapes dynamiques -->
                <div class="mt-6 space-y-3">
                    <template x-for="(step, index) in progressSteps" :key="index">
                        <div class="flex items-center justify-center gap-2 text-sm">
                            <div class="w-2 h-2 rounded-full animate-pulse" 
                                 :style="index <= progressStep ? 'background-color: var(--orange-primary)' : 'background-color: var(--gray-300)'"></div>
                            <span x-text="step" 
                                  :class="index <= progressStep ? 'shimmer-text' : ''"
                                  :style="'color: var(--gray-' + (index <= progressStep ? '700' : '500') + ')'"></span>
                        </div>
                    </template>
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
                            ⚠️ Vous avez atteint la limite hebdomadaire de {{ auth()->user()::WEEKLY_LIMITS['diagnostics'] }} diagnostics
                        </p>
                        <p class="text-xs mt-1" style="color: var(--warning-600);">
                            Prochain reset : {{ now()->startOfWeek()->addWeek()->format('d/m/Y') }}
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

@push('styles')
<style>
/* Styles propres pour la page diagnostic - Dark/Light mode compatible */

/* Cartes de base - utilisation des variables CSS existantes */
.diagnostic-cards .card {
    background: var(--white);
    border: 1px solid var(--gray-200);
    transition: var(--transition);
}

.diagnostic-cards .card:hover {
    border-color: var(--gray-300);
}

/* En-têtes de cartes */
.diagnostic-cards .card-header {
    background: var(--gray-50);
    border-bottom: 1px solid var(--gray-200);
}

.diagnostic-cards .card-title {
    color: var(--gray-900);
    font-weight: var(--font-weight-semibold);
}

.diagnostic-cards .card-description {
    color: var(--gray-500);
    font-size: var(--text-sm);
}

/* Corps de cartes */
.diagnostic-cards .card-body {
    background: var(--white);
}

/* Blocs de contenu - harmonisation */
.diagnostic-cards .card-body > div[class*="bg-gray-50"],
.diagnostic-cards .card-body > div[class*="border-gray-100"] {
    background: var(--gray-50) !important;
    border: 1px solid var(--gray-200) !important;
}

/* Corrections spécifiques pour dark mode - cartes internes */
.diagnostic-cards .bg-gray-50 {
    background: var(--gray-50) !important;
    border-color: var(--gray-200) !important;
}

.diagnostic-cards .border-gray-200 {
    border-color: var(--gray-200) !important;
}

/* Messages et alertes - couleurs système */
.diagnostic-cards div[style*="background: var(--orange-100)"] {
    background: var(--orange-100) !important;
    border: 1px solid var(--orange-200) !important;
}

.diagnostic-cards div[style*="background: var(--success-100)"] {
    background: var(--success-100) !important;
    border: 1px solid var(--green-200) !important;
}

/* Couleurs de texte orange pour les forces */
.diagnostic-cards .text-orange {
    color: var(--orange) !important;
}

/* Message principal - bordure subtile */
.diagnostic-cards .message-principal {
    background: var(--orange-100);
    border: 1px solid rgba(255, 107, 53, 0.3);
}

/* Cartes d'opportunités et partenaires - bordures subtiles */
.diagnostic-cards .opportunity-card,
.diagnostic-cards .partner-card {
    background: var(--white);
    border: 1px solid var(--gray-200);
    transition: var(--transition);
}

.diagnostic-cards .opportunity-card:hover,
.diagnostic-cards .partner-card:hover {
    border-color: var(--gray-300);
}

/* Cartes d'opportunités et partenaires */
.diagnostic-cards .card-body > .space-y-4 > div[class*="border"] {
    background: var(--white);
    border: 1px solid var(--gray-200);
    transition: var(--transition);
}

.diagnostic-cards .card-body > .space-y-4 > div[class*="border"]:hover {
    border-color: var(--gray-300);
}

/* Badges - couleurs système simples */
.badge {
    font-weight: var(--font-weight-medium);
    font-size: var(--text-xs);
    border-radius: var(--radius-sm);
}

.badge-orange,
.badge-blue {
    background: var(--orange-100);
    color: var(--orange-dark);
    border: 1px solid rgba(255, 107, 53, 0.3);
}

.badge-success {
    background: var(--success-100);
    color: var(--success-700);
    border: 1px solid var(--green-200);
}

.badge-warning {
    background: var(--warning-100);
    color: var(--warning-700);
    border: 1px solid var(--warning-200);
}

.badge-gray {
    background: var(--gray-100);
    color: var(--gray-700);
    border: 1px solid var(--gray-200);
}

/* Indicateurs de priorité - orange simple */
.diagnostic-cards .bg-orange.text-white {
    background: var(--orange) !important;
    color: var(--white) !important;
}

/* Sections urgentes - suppression des left-borders, style simple */
.diagnostic-cards div[class*="border-l-3"] {
    border-left: none !important;
    background: var(--warning-50) !important;
    border: 1px solid var(--warning-200) !important;
}


/* En-têtes hover - couleur système */
.diagnostic-cards .card-header:hover {
    background: var(--gray-100);
}

.diagnostic-cards .card-header button:hover i {
    color: var(--gray-700) !important;
}

/* Modal - couleurs système */
.bg-warning-100 {
    background: var(--warning-100) !important;
    border: 1px solid var(--warning-200) !important;
}

/* Boutons - couleurs système */
.diagnostic-cards .btn {
    font-weight: var(--font-weight-medium);
    transition: var(--transition);
}

.diagnostic-cards .btn-secondary {
    background: var(--gray-100);
    color: var(--gray-700);
    border: 1px solid var(--gray-200);
}

.diagnostic-cards .btn-secondary:hover {
    background: var(--gray-200);
    color: var(--gray-900);
}

/* Animation spin */
.diagnostic-cards .smooth-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Shimmer effect simple */
.shimmer-text {
    opacity: 0.7;
    animation: shimmer 2s ease-in-out infinite;
}

@keyframes shimmer {
    0%, 100% { opacity: 0.7; }
    50% { opacity: 1; }
}

/* Amélioration des contrastes - utilisation des variables */
.diagnostic-cards h4,
.diagnostic-cards h5 {
    color: var(--gray-900);
    font-weight: var(--font-weight-semibold);
}

.diagnostic-cards .text-gray-600 {
    color: var(--gray-600) !important;
}

.diagnostic-cards .text-gray-700 {
    color: var(--gray-700) !important;
}

.diagnostic-cards .text-gray-900 {
    color: var(--gray-900) !important;
}

/* Responsive */
@media (max-width: 768px) {
    .diagnostic-cards .card-header,
    .diagnostic-cards .card-body {
        padding: var(--space-4);
    }
}
</style>
@endpush

@endsection