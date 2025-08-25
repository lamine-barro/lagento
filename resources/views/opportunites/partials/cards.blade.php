@forelse($opportunities as $opportunity)
    <div class="opportunity-card" onclick="toggleCard(this)" data-id="{{ $opportunity->id }}">
        <!-- En-tête de la carte -->
        <div class="card-header">
            <h3 class="card-title">{{ $opportunity->titre }}</h3>
            <div class="card-badges">
                <span class="badge badge-{{ strtolower($opportunity->type) }}">
                    {{ $opportunity->type }}
                </span>
                <span class="badge badge-default">{{ $opportunity->statut }}</span>
            </div>
            <div class="institution-info">
                <strong>{{ $opportunity->institution }}</strong> • {{ str_replace('_', ' ', $opportunity->institution_type) }}
            </div>
        </div>
        
        <!-- Aperçu -->
        <div class="card-preview">
            <p class="card-description">
                {{ $opportunity->description }}
            </p>
        </div>
        
        <!-- Meta informations -->
        <div class="card-meta">
            <div class="meta-info">
                @if($opportunity->regions_ciblees && $opportunity->regions_ciblees !== 'National')
                    <span>📍 {{ $opportunity->regions_ciblees }}</span>
                @else
                    <span>📍 National</span>
                @endif
                
                @if($opportunity->date_limite_candidature)
                    <span>📅 {{ $opportunity->date_limite_candidature }}</span>
                @endif
            </div>
            <span class="expand-icon">▼</span>
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
    <div class="no-results">
        <div class="no-results-icon">🔍</div>
        <h3>Aucune opportunité trouvée</h3>
        <p>Essayez de modifier vos critères de recherche ou supprimez les filtres pour voir plus d'opportunités.</p>
    </div>
@endforelse