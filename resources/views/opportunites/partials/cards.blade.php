@forelse($opportunities as $opportunity)
    <div class="opportunity-card" onclick="toggleCard(this)" data-id="{{ $opportunity->id }}">
        <!-- En-tête de la carte -->
        <div class="card-header">
            <h3 class="card-title">{{ $opportunity->titre }}</h3>
            <div class="card-badges">
                <span class="badge badge-{{ strtolower($opportunity->type) }}">
                    @switch($opportunity->type)
                        @case('FINANCEMENT')
                            <i data-lucide="coins" class="w-3 h-3"></i>
                            @break
                        @case('FORMATION')
                            <i data-lucide="graduation-cap" class="w-3 h-3"></i>
                            @break
                        @case('INCUBATION')
                            <i data-lucide="egg" class="w-3 h-3"></i>
                            @break
                        @case('ACCELERATION')
                            <i data-lucide="rocket" class="w-3 h-3"></i>
                            @break
                        @case('CONCOURS')
                            <i data-lucide="trophy" class="w-3 h-3"></i>
                            @break
                        @case('ASSISTANCE_TECHNIQUE')
                            <i data-lucide="headphones" class="w-3 h-3"></i>
                            @break
                        @case('PROMOTION')
                            <i data-lucide="megaphone" class="w-3 h-3"></i>
                            @break
                        @case('STUDIO')
                            <i data-lucide="video" class="w-3 h-3"></i>
                            @break
                        @default
                            <i data-lucide="circle" class="w-3 h-3"></i>
                    @endswitch
                    {{ $opportunity->type }}
                </span>
                <span class="badge badge-status badge-{{ strtolower($opportunity->statut) }}">{{ $opportunity->statut }}</span>
            </div>
            
            <div class="card-meta">
                <span class="meta-item">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    {{ $opportunity->institution }}
                </span>
                <span class="meta-item">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    {{ $opportunity->pays }}
                </span>
            </div>
        </div>
        
        <!-- Contenu condensé visible par défaut -->
        <div class="card-content">
            <div class="description-preview">
                {{ Str::limit($opportunity->description, 120) }}
            </div>
            
            <div class="card-footer">
                <div class="info-badges">
                    @if($opportunity->remuneration && $opportunity->remuneration !== 'Non spécifié')
                        <span class="info-badge">
                            <i data-lucide="banknote" class="w-3 h-3"></i>
                            {{ Str::limit($opportunity->remuneration, 25) }}
                        </span>
                    @endif
                    @if($opportunity->date_limite_candidature && $opportunity->date_limite_candidature !== 'Continu')
                        <span class="info-badge urgency">
                            <i data-lucide="clock" class="w-3 h-3"></i>
                            {{ $opportunity->date_limite_candidature }}
                        </span>
                    @endif
                </div>
                <button class="expand-btn">
                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    <span>Voir détails</span>
                </button>
            </div>
        </div>

        <!-- Contenu étendu -->
        <div class="card-expanded">
            <div class="expanded-content">
                <div class="info-grid">
                    @if($opportunity->remuneration)
                        <div class="info-item">
                            <span class="info-label">💰 Rémunération / Budget</span>
                            <span class="info-value">{{ $opportunity->remuneration }}</span>
                        </div>
                    @endif
                    
                    @if($opportunity->duree)
                        <div class="info-item">
                            <span class="info-label">⏱️ Durée</span>
                            <span class="info-value">{{ $opportunity->duree }}</span>
                        </div>
                    @endif
                    
                    @if($opportunity->nombre_places)
                        <div class="info-item">
                            <span class="info-label">👥 Nombre de places</span>
                            <span class="info-value">{{ $opportunity->nombre_places }}</span>
                        </div>
                    @endif
                    
                    @if($opportunity->date_debut)
                        <div class="info-item">
                            <span class="info-label">📅 Date de début</span>
                            <span class="info-value">{{ $opportunity->date_debut }}</span>
                        </div>
                    @endif
                    
                    @if($opportunity->contact_email_enrichi)
                        <div class="info-item">
                            <span class="info-label">📧 Contact</span>
                            <span class="info-value">{{ $opportunity->contact_email_enrichi }}</span>
                        </div>
                    @endif
                </div>
                
                @if($opportunity->secteurs)
                    <div class="info-item">
                        <span class="info-label">🏢 Secteurs d'activité</span>
                        <div class="secteurs-list">
                            @foreach($opportunity->secteurs_array as $secteur)
                                <span class="secteur-tag">{{ str_replace('_', ' ', $secteur) }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                @if($opportunity->criteres_eligibilite)
                    <div class="info-item">
                        <span class="info-label">✅ Critères d'éligibilité</span>
                        <span class="info-value">{{ $opportunity->criteres_eligibilite }}</span>
                    </div>
                @endif
            </div>
            
            @if($opportunity->lien_externe)
                <a href="{{ $opportunity->lien_externe }}" 
                   target="_blank" 
                   class="external-link"
                   onclick="event.stopPropagation()">
                    Voir les détails officiels
                    <span>↗</span>
                </a>
            @endif
        </div>
    </div>
@empty
    <div class="col-span-full text-center py-12">
        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
            <i data-lucide="search-x" class="w-8 h-8 text-gray-400"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Aucune opportunité trouvée</h3>
        <p class="text-gray-500 dark:text-gray-400">Essayez de modifier vos filtres ou votre recherche.</p>
    </div>
@endforelse