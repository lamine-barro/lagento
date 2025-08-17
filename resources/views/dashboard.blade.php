@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
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
    toggleSection(section) {
        this.sectionsState[section] = !this.sectionsState[section];
    },
    
    refreshDiagnostic() {
        this.$dispatch('open-diagnostic-modal');
    },
    
    runDiagnostic() {
        // Ici on appellera l'agent de diagnostic
        fetch('{{ route("diagnostic.run") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Erreur lors du diagnostic');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du diagnostic');
        });
    }
}">
    <!-- En-tête -->
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-primary mb-2">Diagnostic</h1>
            <p class="text-secondary">{{ isset($analytics) ? 'Dernière mise à jour: ' . $analytics->metadata->derniere_maj : 'Vue d\'ensemble de votre activité entrepreneuriale' }}</p>
        </div>
        
        <!-- Actions -->
        <div class="flex items-center gap-2">
            <button 
                @click="refreshDiagnostic()"
                class="px-4 py-3 rounded-md border transition-colors flex items-center gap-2 text-sm font-medium"
                style="border-color: var(--gray-300); color: var(--gray-700);"
                title="Rafraîchir le diagnostic"
            >
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Rafraîchir</span>
            </button>
            
            <a 
                href="{{ route('onboarding.step3') }}"
                class="px-4 py-3 rounded-md border transition-colors flex items-center gap-2 text-sm font-medium hover:bg-gray-50"
                style="border-color: var(--gray-300); color: var(--gray-700);"
                title="Éditer les informations du projet"
            >
                <i data-lucide="edit" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Éditer projet</span>
            </a>
            
            <a 
                href="{{ route('documents.index') }}"
                class="px-4 py-3 rounded-md border transition-colors flex items-center gap-2 text-sm font-medium hover:bg-gray-50"
                style="border-color: var(--gray-300); color: var(--gray-700);"
                title="Gérer les documents"
            >
                <i data-lucide="folder-open" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Documents</span>
            </a>
        </div>
    </div>
    
    @if(isset($analytics))
    <div class="space-y-6">
        <!-- Résumé Exécutif -->
        <div class="card">
            <div class="card-header cursor-pointer" @click="toggleSection('resume')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="star" class="w-5 h-5 text-orange"></i>
                            Résumé Exécutif
                        </h3>
                        <p class="card-description">Score progression: {{ $analytics->resume_executif->score_progression ?? 0 }}/100</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.resume ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.resume" class="card-body">
                <div class="mb-4 p-4 rounded-lg" style="background: var(--orange-100);">
                    <h4 class="font-medium text-orange mb-2">Message Principal</h4>
                    <p class="text-sm">{{ $analytics->resume_executif->message_principal ?? 'Analyse en cours...' }}</p>
                </div>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium mb-3">3 Actions Clés</h4>
                        <div class="space-y-2">
                            @foreach(($analytics->resume_executif->trois_actions_cles ?? []) as $action)
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
                            {{ $analytics->resume_executif->opportunite_du_mois ?? 'Aucune opportunité identifiée' }}
                        </p>
                        
                        @if(isset($analytics->resume_executif->alerte_importante))
                        <h4 class="font-medium mb-2 mt-4">⚠️ Alerte Importante</h4>
                        <p class="text-sm p-3 rounded" style="background: var(--warning-100); color: var(--warning-700);">
                            {{ $analytics->resume_executif->alerte_importante }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Profil Entrepreneur -->
        <div class="card">
            <div class="card-header cursor-pointer" @click="toggleSection('profil')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="user" class="w-5 h-5 text-orange"></i>
                            Profil Entrepreneur
                        </h3>
                        <p class="card-description">Niveau: {{ ucfirst($analytics->profil_entrepreneur->niveau_global ?? 'Non défini') }} | Score: {{ $analytics->profil_entrepreneur->score_potentiel ?? 0 }}/100</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.profil ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.profil" class="card-body">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium mb-3">🎯 Forces Identifiées</h4>
                        <div class="space-y-2">
                            @foreach(($analytics->profil_entrepreneur->forces ?? []) as $force)
                            <div class="p-3 rounded" style="background: var(--gray-50);">
                                <span class="font-medium text-orange">{{ $force->domaine }}</span>
                                <p class="text-sm text-muted mt-1">{{ $force->description }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-3">📈 Axes de Progression</h4>
                        <div class="space-y-2">
                            @foreach(($analytics->profil_entrepreneur->axes_progression ?? []) as $axe)
                            <div class="p-3 rounded" style="background: var(--gray-50);">
                                <span class="font-medium">{{ $axe->domaine }}</span>
                                <p class="text-sm text-muted mt-1">{{ $axe->action_suggeree }}</p>
                                <span class="text-xs badge badge-{{ $axe->impact === 'immédiat' ? 'orange' : ($axe->impact === 'court_terme' ? 'blue' : 'gray') }}">{{ $axe->impact }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Diagnostic Projet -->
        <div class="card">
            <div class="card-header cursor-pointer" @click="toggleSection('diagnostic')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="activity" class="w-5 h-5 text-orange"></i>
                            Diagnostic Projet
                        </h3>
                        <p class="card-description">Santé: {{ $analytics->diagnostic_projet->score_sante ?? 0 }}/100 | Maturité: {{ ucfirst($analytics->diagnostic_projet->niveau_maturite ?? 'Non défini') }}</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.diagnostic ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.diagnostic" class="card-body">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium mb-3">📊 Indicateurs Clés</h4>
                        <div class="space-y-3">
                            @if(isset($analytics->diagnostic_projet->indicateurs_cles))
                            @foreach($analytics->diagnostic_projet->indicateurs_cles as $domaine => $indicateur)
                            <div class="p-3 rounded" style="background: var(--gray-50);">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium capitalize">{{ $domaine }}</span>
                                    <span class="badge badge-{{ $indicateur->statut === 'ok' ? 'success' : ($indicateur->statut === 'en_cours' ? 'warning' : 'gray') }}">{{ $indicateur->statut }}</span>
                                </div>
                                @if(isset($indicateur->urgence))
                                <p class="text-xs text-muted">Urgence: {{ $indicateur->urgence }}</p>
                                @endif
                            </div>
                            @endforeach
                            @endif
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-3">🎯 Prochaines Étapes</h4>
                        <div class="space-y-2">
                            @foreach(($analytics->diagnostic_projet->prochaines_etapes ?? []) as $etape)
                            <div class="p-3 rounded" style="background: var(--gray-50);">
                                <div class="flex items-start gap-2">
                                    <span class="bg-orange text-white text-xs rounded-full w-5 h-5 flex items-center justify-center flex-shrink-0">{{ $etape->priorite }}</span>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium">{{ $etape->action }}</p>
                                        <p class="text-xs text-muted">Délai: {{ $etape->delai }}</p>
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
        <div class="card">
            <div class="card-header cursor-pointer" @click="toggleSection('opportunites')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="target" class="w-5 h-5 text-orange"></i>
                            Opportunités Matchées
                        </h3>
                        <p class="card-description">{{ $analytics->opportunites_matchees->nombre_total ?? 0 }} opportunités identifiées</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.opportunites ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.opportunites" class="card-body">
                <div class="space-y-4">
                    @foreach(($analytics->opportunites_matchees->top_opportunites ?? []) as $opportunite)
                    <div class="p-4 rounded-lg border" style="border-color: var(--gray-200);">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-medium text-orange">{{ $opportunite->titre }}</h4>
                            <span class="badge badge-{{ $opportunite->urgence === 'candidater_avant_7j' ? 'orange' : 'blue' }}">{{ $opportunite->urgence }}</span>
                        </div>
                        <p class="text-sm text-muted mb-2">{{ $opportunite->institution }} | Compatibilité: {{ $opportunite->score_compatibilite }}%</p>
                        <p class="text-sm mb-3">{{ $opportunite->pourquoi_vous }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-orange">{{ $opportunite->montant_ou_valeur }}</span>
                            @if(isset($opportunite->lien))
                            <a href="{{ $opportunite->lien }}" class="btn btn-sm btn-secondary">Candidater</a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Insights Marché -->
        <div class="card">
            <div class="card-header cursor-pointer" @click="toggleSection('marche')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="trending-up" class="w-5 h-5 text-orange"></i>
                            Insights Marché
                        </h3>
                        <p class="card-description">Position: {{ $analytics->insights_marche->position_concurrentielle->votre_place ?? 'Non définie' }}</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.marche ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.marche" class="card-body">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium mb-3">💰 Taille du Marché</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm">Local:</span>
                                <span class="text-sm font-medium text-orange">{{ $analytics->insights_marche->taille_marche->local ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm">Potentiel:</span>
                                <span class="text-sm font-medium text-orange">{{ $analytics->insights_marche->taille_marche->potentiel ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm">Croissance:</span>
                                <span class="text-sm font-medium text-success">{{ $analytics->insights_marche->taille_marche->croissance ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-3">🎯 Zones d'Opportunités</h4>
                        <div class="space-y-2">
                            @foreach(($analytics->insights_marche->zones_opportunites ?? []) as $zone)
                            <div class="p-3 rounded" style="background: var(--gray-50);">
                                <span class="font-medium">{{ $zone->region }}</span>
                                <p class="text-sm text-muted">{{ $zone->raison }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Réglementations -->
        <div class="card">
            <div class="card-header cursor-pointer" @click="toggleSection('regulations')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-5 h-5 text-orange"></i>
                            Conformité Réglementaire
                        </h3>
                        <p class="card-description">Statut: {{ ucfirst($analytics->regulations->conformite_globale ?? 'Non défini') }}</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.regulations ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.regulations" class="card-body">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium mb-3 text-orange">🚨 Urgent</h4>
                        <div class="space-y-2">
                            @foreach(($analytics->regulations->urgent ?? []) as $urgent)
                            <div class="p-3 rounded border-l-4" style="background: var(--warning-100); border-color: var(--warning);">
                                <span class="font-medium">{{ $urgent->obligation }}</span>
                                <p class="text-sm text-muted">Deadline: {{ $urgent->deadline }}</p>
                                <p class="text-sm">Coût: {{ $urgent->cout }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-3">📅 À Prévoir</h4>
                        <div class="space-y-2">
                            @foreach(($analytics->regulations->a_prevoir ?? []) as $prevoir)
                            <div class="p-3 rounded" style="background: var(--gray-50);">
                                <span class="font-medium">{{ $prevoir->obligation }}</span>
                                <p class="text-sm text-muted">Échéance: {{ $prevoir->echeance }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Partenaires Suggérés -->
        <div class="card">
            <div class="card-header cursor-pointer" @click="toggleSection('partenaires')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="users" class="w-5 h-5 text-orange"></i>
                            Partenaires Suggérés
                        </h3>
                        <p class="card-description">{{ $analytics->partenaires_suggeres->nombre_matches ?? 0 }} partenaires potentiels</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.partenaires ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.partenaires" class="card-body">
                <div class="space-y-4">
                    @foreach(($analytics->partenaires_suggeres->top_partenaires ?? []) as $partenaire)
                    <div class="p-4 rounded-lg border" style="border-color: var(--gray-200);">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-medium">{{ $partenaire->nom_projet }}</h4>
                            <span class="badge badge-blue">{{ $partenaire->score_pertinence }}% match</span>
                        </div>
                        <p class="text-sm text-muted mb-2">{{ $partenaire->secteur }} | {{ $partenaire->localisation }}</p>
                        <p class="text-sm mb-2">{{ $partenaire->proposition_collaboration }}</p>
                        <span class="text-xs badge badge-{{ $partenaire->type_synergie === 'strategique' ? 'orange' : 'gray' }}">{{ $partenaire->type_synergie }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-body text-center py-12">
            <i data-lucide="bar-chart-3" class="w-16 h-16 mx-auto mb-4 text-gray-300"></i>
            <h3 class="text-lg font-medium text-primary mb-2">Analytics en cours de génération</h3>
            <p class="text-muted mb-6">Vos données entrepreneuriales sont en cours d'analyse. Revenez dans quelques minutes.</p>
            <a href="{{ route('chat') }}" class="btn btn-primary">
                Démarrer une conversation
            </a>
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
                            ⚠️ Vous avez atteint la limite mensuelle de 3 diagnostics
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
                        @click="runDiagnostic(); open = false"
                        class="btn btn-primary flex-1"
                    >
                        Lancer le diagnostic
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection