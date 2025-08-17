@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container section">
    <!-- En-tête -->
    <div class="mb-6">
        <h1 class="text-primary mb-2">Dashboard</h1>
        <p class="text-secondary">Vue d'ensemble de votre activité entrepreneuriale</p>
    </div>
    
    <!-- Grille des cartes -->
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        
        <!-- Opportunités matchées -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Opportunités</h3>
                <p class="card-description">{{ isset($opportunities) ? $opportunities->count() : 0 }} disponibles</p>
            </div>
            
            <div class="card-body">
                @if(isset($opportunities) && $opportunities->count() > 0)
                    <div class="space-y-3">
                        @foreach($opportunities->take(3) as $opportunity)
                        <div class="flex items-start gap-3 p-3 bg-gray-100 rounded">
                            <div class="w-2 h-2 bg-orange rounded-full mt-2 flex-shrink-0"></div>
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-primary">{{ $opportunity->titre }}</h4>
                                <p class="text-xs text-muted mt-1">{{ Str::limit($opportunity->description, 60) }}</p>
                                @if($opportunity->deadline)
                                    <span class="badge badge-gray mt-2">{{ $opportunity->deadline }}</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                        
                        @if($opportunities->count() > 3)
                        <p class="text-xs text-center text-muted">+{{ $opportunities->count() - 3 }} autres opportunités</p>
                        @endif
                    </div>
                @else
                    <div class="text-center py-6">
                        <svg class="w-8 h-8 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <p class="text-muted">Aucune opportunité disponible</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Analytics -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Analytics</h3>
                <p class="card-description">Votre activité récente</p>
            </div>
            
            <div class="card-body">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-semibold text-orange">{{ isset($userAnalytics) ? $userAnalytics->interactions_count ?? 0 : 0 }}</div>
                        <div class="text-xs text-muted">Interactions</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-semibold text-orange">{{ isset($userAnalytics) ? $userAnalytics->projets_count ?? 0 : 0 }}</div>
                        <div class="text-xs text-muted">Projets</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Projets récents -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Projets récents</h3>
                <p class="card-description">Vos derniers projets</p>
            </div>
            
            <div class="card-body">
                @if(isset($recentProjects) && $recentProjects->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentProjects->take(3) as $project)
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 bg-success rounded-full flex-shrink-0"></div>
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-primary">{{ $project->nom }}</h4>
                                <p class="text-xs text-muted">{{ $project->secteur ?? 'Secteur non défini' }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <svg class="w-8 h-8 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-muted">Aucun projet créé</p>
                    </div>
                @endif
            </div>
            
            @if(Route::has('projets.index'))
            <div class="card-footer">
                <a href="{{ route('projets.index') }}" class="btn btn-secondary btn-sm w-full">
                    Voir tous les projets
                </a>
            </div>
            @endif
        </div>
        
        <!-- Conversations récentes -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Conversations</h3>
                <p class="card-description">{{ isset($userAnalytics) ? $userAnalytics->conversations_count ?? 0 : 0 }} conversations</p>
            </div>
            
            <div class="card-body">
                @if(isset($recentConversations) && $recentConversations->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentConversations->take(3) as $conversation)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-orange rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-xs text-white font-medium">IA</span>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-primary">{{ $conversation->titre }}</h4>
                                <p class="text-xs text-muted">{{ $conversation->updated_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <svg class="w-8 h-8 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <p class="text-muted">Aucune conversation</p>
                    </div>
                @endif
            </div>
            
            @if(Route::has('conversations.index'))
            <div class="card-footer">
                <a href="{{ route('conversations.index') }}" class="btn btn-secondary btn-sm w-full">
                    Voir toutes les conversations
                </a>
            </div>
            @endif
        </div>
        
        <!-- Actions rapides -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Actions rapides</h3>
                <p class="card-description">Commencer quelque chose de nouveau</p>
            </div>
            
            <div class="card-body">
                <div class="space-y-3">
                    <a href="{{ route('chat') }}" class="btn btn-primary w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Nouvelle conversation
                    </a>
                    
                    @if(Route::has('projets.create'))
                    <a href="{{ route('projets.create') }}" class="btn btn-secondary w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nouveau projet
                    </a>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Conformité légale -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Conformité légale</h3>
                <p class="card-description">Statut de vos obligations</p>
            </div>
            
            <div class="card-body">
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-green-50 rounded">
                        <span class="text-sm font-medium text-primary">RCCM</span>
                        <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded">
                        <span class="text-sm font-medium text-primary">DFE</span>
                        <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-green-50 rounded">
                        <span class="text-sm font-medium text-primary">CNPS</span>
                        <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistiques détaillées -->
        <div class="card md:col-span-2 lg:col-span-3">
            <div class="card-header">
                <h3 class="card-title">Statistiques détaillées</h3>
                <p class="card-description">Vue d'ensemble de votre activité</p>
            </div>
            
            <div class="card-body">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-semibold text-orange mb-2">{{ isset($userAnalytics) ? $userAnalytics->interactions_count ?? 0 : 0 }}</div>
                        <div class="text-sm text-muted">Interactions avec l'IA</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-semibold text-orange mb-2">{{ isset($userAnalytics) ? $userAnalytics->projets_count ?? 0 : 0 }}</div>
                        <div class="text-sm text-muted">Projets créés</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-semibold text-orange mb-2">{{ isset($userAnalytics) ? $userAnalytics->conversations_count ?? 0 : 0 }}</div>
                        <div class="text-sm text-muted">Conversations</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-semibold text-orange mb-2">{{ isset($userAnalytics) ? $userAnalytics->documents_count ?? 0 : 0 }}</div>
                        <div class="text-sm text-muted">Documents analysés</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS Grid pour responsive -->
<style>
.grid {
  display: grid;
}

.grid-cols-2 {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.space-y-3 > * + * {
  margin-top: 0.75rem;
}

@media (min-width: 768px) {
  .md\:grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  
  .md\:grid-cols-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
  
  .md\:col-span-2 {
    grid-column: span 2 / span 2;
  }
}

@media (min-width: 1024px) {
  .lg\:grid-cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
  
  .lg\:col-span-3 {
    grid-column: span 3 / span 3;
  }
}

.w-full {
  width: 100%;
}

.bg-green-50 {
  background-color: #f0fdf4;
}

.bg-yellow-50 {
  background-color: #fefce8;
}

.text-success {
  color: var(--success);
}

.text-warning {
  color: var(--warning);
}
</style>

@endsection