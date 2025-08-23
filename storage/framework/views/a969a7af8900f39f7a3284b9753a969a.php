<?php $__env->startSection('seo_title', 'Opportunités d\'Entrepreneuriat - <?php echo e($totalOpportunities); ?>+ opportunités | AgentO'); ?>
<?php $__env->startSection('meta_description', 'Découvrez <?php echo e($totalOpportunities); ?>+ opportunités officielles de financement, incubation, accélération et formation pour entrepreneurs ivoiriens. Ministères, banques, fonds d\'investissement.'); ?>
<?php $__env->startSection('meta_keywords', 'opportunités financement côte ivoire, startup incubation, ministère agriculture, aej, fafci, banque mondiale'); ?>
<?php $__env->startSection('title', 'Opportunités d\'Entrepreneuriat - AgentO'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .opportunities-page {
        min-height: 100vh;
        background: white;
        padding: 2rem 0;
        transition: background-color 0.3s ease;
    }
    
    [data-theme="dark"] .opportunities-page {
        background: #0a0a0a;
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }
    
    .page-header {
        text-align: center;
        margin-bottom: 3rem;
    }
    
    .page-title {
        font-size: 3rem;
        font-weight: bold;
        color: var(--orange);
        margin-bottom: 1rem;
        line-height: 1.2;
    }
    
    [data-theme="dark"] .page-title {
        color: white;
    }
    
    .page-subtitle {
        font-size: 1.25rem;
        color: var(--gray-600);
        max-width: 700px;
        margin: 0 auto 2rem;
        line-height: 1.6;
    }
    
    [data-theme="dark"] .page-subtitle {
        color: var(--gray-400);
    }
    
    .stats-highlight {
        display: inline-block;
        background: var(--orange);
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 2rem;
        font-weight: bold;
        margin: 0 0.5rem;
        font-size: 1.1rem;
    }
    
    /* Barre de recherche */
    .search-section {
        margin-bottom: 2rem;
        display: flex;
        justify-content: center;
    }
    
    .search-form {
        display: flex;
        gap: 1rem;
        max-width: 500px;
        width: 100%;
    }
    
    .search-input {
        flex: 1;
        padding: 0.875rem 1.25rem;
        border: 2px solid var(--gray-200);
        border-radius: 0.5rem;
        font-size: 1rem;
        background: var(--white);
        color: var(--gray-900);
        transition: all 0.3s ease;
    }
    
    [data-theme="dark"] .search-input {
        background: var(--gray-800);
        border-color: var(--gray-700);
        color: var(--gray-100);
    }
    
    .search-input:focus {
        border-color: var(--orange);
        outline: none;
    }
    
    .search-btn {
        padding: 0.875rem 1.5rem;
        background: var(--orange);
        color: white;
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .search-btn:hover {
        background: var(--orange-dark);
        transform: translateY(-1px);
    }
    
    /* Tags Pills Scrollables */
    .filter-tags {
        margin-bottom: 2rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .tags-container {
        display: flex;
        gap: 0.75rem;
        padding: 1rem 0;
        min-width: max-content;
    }
    
    .tag-pill {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 1rem;
        background: var(--white);
        border: 2px solid var(--gray-200);
        border-radius: 0.5rem;
        color: var(--gray-700);
        text-decoration: none;
        transition: all 0.3s ease;
        white-space: nowrap;
        font-weight: 500;
        font-size: 0.875rem;
    }
    
    [data-theme="dark"] .tag-pill {
        background: var(--gray-800);
        border-color: var(--gray-700);
        color: var(--gray-300);
    }
    
    .tag-pill:hover {
        border-color: var(--orange);
        color: var(--orange);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.2);
    }
    
    .tag-pill.active {
        background: var(--orange);
        border-color: var(--orange);
        color: white;
    }
    
    .tag-count {
        background: rgba(0, 0, 0, 0.1);
        padding: 0.125rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        min-width: 24px;
        text-align: center;
        font-weight: 600;
    }
    
    .tag-pill.active .tag-count {
        background: rgba(255, 255, 255, 0.2);
    }
    
    /* Grille des Cartes */
    .opportunities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }
    
    /* Cartes Opportunités */
    .opportunity-card {
        background: var(--white);
        border-radius: 1rem;
        border: 1px solid var(--gray-200);
        overflow: hidden;
        transition: all 0.3s ease;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    [data-theme="dark"] .opportunity-card {
        background: #1a1a1a;
        border-color: #333;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }
    
    .opportunity-card:hover {
        border-color: var(--orange);
        box-shadow: 0 8px 25px rgba(255, 107, 53, 0.15);
        transform: translateY(-2px);
    }
    
    .card-header {
        padding: 1.5rem 1.5rem 1rem;
        border-bottom: 1px solid var(--gray-100);
    }
    
    [data-theme="dark"] .card-header {
        border-color: #333;
    }
    
    .card-title {
        font-size: 1.25rem;
        font-weight: bold;
        color: var(--gray-900);
        margin-bottom: 0.75rem;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    [data-theme="dark"] .card-title {
        color: #f5f5f5;
    }
    
    .card-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    
    .badge-financement { background: rgba(59, 130, 246, 0.1); color: #1e40af; }
    .badge-incubation { background: rgba(16, 185, 129, 0.1); color: #065f46; }
    .badge-formation { background: rgba(245, 158, 11, 0.1); color: #92400e; }
    .badge-concours { background: rgba(236, 72, 153, 0.1); color: #be185d; }
    .badge-acceleration { background: rgba(239, 68, 68, 0.1); color: #991b1b; }
    .badge-default { background: rgba(107, 114, 128, 0.1); color: #374151; }
    
    [data-theme="dark"] .badge-financement { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
    [data-theme="dark"] .badge-incubation { background: rgba(16, 185, 129, 0.2); color: #34d399; }
    [data-theme="dark"] .badge-formation { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
    [data-theme="dark"] .badge-concours { background: rgba(236, 72, 153, 0.2); color: #f472b6; }
    [data-theme="dark"] .badge-acceleration { background: rgba(239, 68, 68, 0.2); color: #f87171; }
    [data-theme="dark"] .badge-default { background: rgba(107, 114, 128, 0.2); color: #9ca3af; }
    
    .institution-info {
        font-size: 0.9rem;
        color: var(--gray-600);
        font-weight: 500;
    }
    
    [data-theme="dark"] .institution-info {
        color: #a0a0a0;
    }
    
    .card-preview {
        padding: 1rem 1.5rem;
    }
    
    .card-description {
        color: var(--gray-600);
        line-height: 1.6;
        font-size: 0.95rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    [data-theme="dark"] .card-description {
        color: #cccccc;
    }
    
    .card-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        background: #f8f9fa;
        font-size: 0.875rem;
        color: var(--gray-500);
    }
    
    [data-theme="dark"] .card-meta {
        background: #2a2a2a;
        color: #808080;
    }
    
    .meta-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .expand-icon {
        color: var(--orange);
        font-size: 1.25rem;
        transition: transform 0.3s ease;
        flex-shrink: 0;
    }
    
    .opportunity-card.expanded .expand-icon {
        transform: rotate(180deg);
    }
    
    /* Contenu Étendu */
    .card-expanded {
        display: none;
        padding: 1.5rem;
        border-top: 1px solid var(--gray-100);
        background: #f8f9fa;
    }
    
    [data-theme="dark"] .card-expanded {
        border-color: #333;
        background: #111;
    }
    
    .opportunity-card.expanded .card-expanded {
        display: block;
    }
    
    .expanded-content {
        margin-bottom: 1.5rem;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .info-label {
        font-weight: 600;
        color: var(--gray-700);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    
    [data-theme="dark"] .info-label {
        color: #f5f5f5;
    }
    
    .info-value {
        color: var(--gray-600);
        font-size: 0.9rem;
        line-height: 1.5;
    }
    
    [data-theme="dark"] .info-value {
        color: #cccccc;
    }
    
    .secteurs-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .secteur-tag {
        background: rgba(255, 107, 53, 0.1);
        color: var(--orange-dark);
        padding: 0.25rem 0.6rem;
        border-radius: 0.5rem;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    [data-theme="dark"] .secteur-tag {
        background: rgba(255, 107, 53, 0.2);
        color: var(--orange-light);
    }
    
    .external-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--orange);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    
    .external-link:hover {
        background: var(--orange-dark);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        color: white;
    }
    
    /* Load More Section */
    .load-more-section {
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid var(--gray-200);
        text-align: center;
    }
    
    [data-theme="dark"] .load-more-section {
        border-color: #333;
    }
    
    .pagination-info {
        margin-bottom: 2rem;
        color: var(--gray-600);
        font-size: 0.95rem;
    }
    
    [data-theme="dark"] .pagination-info {
        color: #cccccc;
    }
    
    .load-more-container {
        display: flex;
        justify-content: center;
    }
    
    .load-more-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem 2rem;
        background: var(--orange);
        color: white;
        border: none;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(255, 107, 53, 0.2);
    }
    
    .load-more-btn:hover {
        background: var(--orange-dark);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
    }
    
    .load-more-btn:disabled {
        background: var(--gray-400);
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    
    [data-theme="dark"] .load-more-btn:disabled {
        background: #666;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    /* Messages d'erreur ou vides */
    .no-results {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--gray-500);
    }
    
    [data-theme="dark"] .no-results {
        color: var(--gray-400);
    }
    
    .no-results-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .no-results h3 {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        color: var(--gray-700);
    }
    
    [data-theme="dark"] .no-results h3 {
        color: var(--gray-300);
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .opportunities-page {
            padding: 1rem 0;
        }
        
        .container {
            padding: 0 1rem;
        }
        
        .page-title {
            font-size: 2.25rem;
        }
        
        .search-form {
            flex-direction: column;
        }
        
        .opportunities-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .card-header, .card-preview, .card-meta, .card-expanded {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .meta-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
    }
    
    @media (max-width: 640px) {
        .page-title {
            font-size: 1.875rem;
        }
        
        .stats-highlight {
            display: block;
            margin: 0.5rem auto;
        }
    }
</style>

<div class="opportunities-page">
    <div class="container">
        <!-- En-tête -->
        <div class="page-header">
            <!-- Logo -->
            <div style="margin-bottom: 2rem;">
                <?php if (isset($component)) { $__componentOriginal987d96ec78ed1cf75b349e2e5981978f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal987d96ec78ed1cf75b349e2e5981978f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.logo','data' => ['size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'lg']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal987d96ec78ed1cf75b349e2e5981978f)): ?>
<?php $attributes = $__attributesOriginal987d96ec78ed1cf75b349e2e5981978f; ?>
<?php unset($__attributesOriginal987d96ec78ed1cf75b349e2e5981978f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal987d96ec78ed1cf75b349e2e5981978f)): ?>
<?php $component = $__componentOriginal987d96ec78ed1cf75b349e2e5981978f; ?>
<?php unset($__componentOriginal987d96ec78ed1cf75b349e2e5981978f); ?>
<?php endif; ?>
            </div>
            
            <h1 class="page-title">
            <?php echo e(number_format($totalOpportunities)); ?> opportunités pour la jeunesse entrepreneuriale ivorienne
            </h1>
        </div>

        <!-- Barre de recherche -->
        <div class="search-section">
            <form method="GET" class="search-form">
                <input type="text" 
                       name="search" 
                       value="<?php echo e($search); ?>" 
                       placeholder="Rechercher par titre, institution, secteur..." 
                       class="search-input">
                <button type="submit" class="search-btn">Rechercher</button>
                
                <!-- Maintenir les filtres existants -->
                <?php if($selectedType !== 'tous'): ?>
                    <input type="hidden" name="type" value="<?php echo e($selectedType); ?>">
                <?php endif; ?>
                <?php if($selectedInstitutionType !== 'tous'): ?>
                    <input type="hidden" name="institution_type" value="<?php echo e($selectedInstitutionType); ?>">
                <?php endif; ?>
            </form>
        </div>

        <!-- Tags de Filtres par Type -->
        <div class="filter-tags">
            <div class="tags-container">
                <a href="<?php echo e(request()->fullUrlWithQuery(['type' => null, 'page' => null])); ?>" 
                   class="tag-pill <?php echo e($selectedType === 'tous' ? 'active' : ''); ?>">
                    <i data-lucide="grid-3x3" style="width: 16px; height: 16px;"></i>
                    <span>Tous</span>
                    <span class="tag-count"><?php echo e(array_sum($typeStats)); ?></span>
                </a>
                
                <?php $__currentLoopData = $typeStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(request()->fullUrlWithQuery(['type' => $type, 'page' => null])); ?>" 
                       class="tag-pill <?php echo e($selectedType === $type ? 'active' : ''); ?>">
                        <?php switch($type):
                            case ('FINANCEMENT'): ?>
                                <i data-lucide="coins" style="width: 16px; height: 16px;"></i>
                                <?php break; ?>
                            <?php case ('INCUBATION'): ?>
                                <i data-lucide="egg" style="width: 16px; height: 16px;"></i>
                                <?php break; ?>
                            <?php case ('FORMATION'): ?>
                                <i data-lucide="graduation-cap" style="width: 16px; height: 16px;"></i>
                                <?php break; ?>
                            <?php case ('CONCOURS'): ?>
                                <i data-lucide="trophy" style="width: 16px; height: 16px;"></i>
                                <?php break; ?>
                            <?php case ('ACCELERATION'): ?>
                                <i data-lucide="rocket" style="width: 16px; height: 16px;"></i>
                                <?php break; ?>
                            <?php default: ?>
                                <i data-lucide="circle" style="width: 16px; height: 16px;"></i>
                        <?php endswitch; ?>
                        <span><?php echo e($type); ?></span>
                        <span class="tag-count"><?php echo e($count); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <!-- Tags de Filtres par Type d'Institution -->
        <?php if(!empty($institutionTypeStats)): ?>
        <div class="filter-tags">
            <div class="tags-container">
                <a href="<?php echo e(request()->fullUrlWithQuery(['institution_type' => null, 'page' => null])); ?>" 
                   class="tag-pill <?php echo e($selectedInstitutionType === 'tous' ? 'active' : ''); ?>">
                    <i data-lucide="building-2" style="width: 16px; height: 16px;"></i>
                    <span>Toutes institutions</span>
                    <span class="tag-count"><?php echo e(array_sum($institutionTypeStats)); ?></span>
                </a>
                
                <?php $__currentLoopData = $institutionTypeStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $instType => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(request()->fullUrlWithQuery(['institution_type' => $instType, 'page' => null])); ?>" 
                       class="tag-pill <?php echo e($selectedInstitutionType === $instType ? 'active' : ''); ?>">
                        <?php switch($instType):
                            case ('MINISTERE_AGENCE'): ?>
                                <i data-lucide="landmark" style="width: 16px; height: 16px;"></i>
                                <?php break; ?>
                            <?php case ('BANQUE_DEVELOPPEMENT'): ?>
                                <i data-lucide="university" style="width: 16px; height: 16px;"></i>
                                <?php break; ?>
                            <?php case ('FONDS_INVESTISSEMENT'): ?>
                                <i data-lucide="trending-up" style="width: 16px; height: 16px;"></i>
                                <?php break; ?>
                            <?php case ('INCUBATEUR_ACCELERATEUR'): ?>
                                <i data-lucide="zap" style="width: 16px; height: 16px;"></i>
                                <?php break; ?>
                            <?php case ('ASSOCIATION_ENTREPRENEURIALE'): ?>
                                <i data-lucide="users" style="width: 16px; height: 16px;"></i>
                                <?php break; ?>
                            <?php case ('MICROFINANCE'): ?>
                                <i data-lucide="piggy-bank" style="width: 16px; height: 16px;"></i>
                                <?php break; ?>
                            <?php case ('ORGANISATION_INTERNATIONALE'): ?>
                                <i data-lucide="globe" style="width: 16px; height: 16px;"></i>
                                <?php break; ?>
                            <?php default: ?>
                                <i data-lucide="building" style="width: 16px; height: 16px;"></i>
                        <?php endswitch; ?>
                        <span><?php echo e(str_replace('_', ' ', $instType)); ?></span>
                        <span class="tag-count"><?php echo e($count); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Grille des Opportunités -->
        <?php if($opportunities->isNotEmpty()): ?>
            <div class="opportunities-grid" id="opportunitiesGrid">
                <?php $__currentLoopData = $opportunities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opportunity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="opportunity-card" onclick="toggleCard(this)" data-id="<?php echo e($opportunity->id); ?>">
                        <!-- En-tête de la carte -->
                        <div class="card-header">
                            <h3 class="card-title"><?php echo e($opportunity->titre); ?></h3>
                            <div class="card-badges">
                                <span class="badge badge-<?php echo e(strtolower($opportunity->type)); ?>">
                                    <?php echo e($opportunity->type); ?>

                                </span>
                                <span class="badge badge-default"><?php echo e($opportunity->statut); ?></span>
                            </div>
                            <div class="institution-info">
                                <strong><?php echo e($opportunity->institution); ?></strong> • <?php echo e(str_replace('_', ' ', $opportunity->institution_type)); ?>

                            </div>
                        </div>
                        
                        <!-- Aperçu -->
                        <div class="card-preview">
                            <p class="card-description">
                                <?php echo e($opportunity->description); ?>

                            </p>
                        </div>
                        
                        <!-- Meta informations -->
                        <div class="card-meta">
                            <div class="meta-info">
                                <?php if($opportunity->regions_ciblees && $opportunity->regions_ciblees !== 'National'): ?>
                                    <span>📍 <?php echo e($opportunity->regions_ciblees); ?></span>
                                <?php else: ?>
                                    <span>📍 National</span>
                                <?php endif; ?>
                                
                                <?php if($opportunity->date_limite_candidature): ?>
                                    <span>📅 <?php echo e($opportunity->date_limite_candidature); ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="expand-icon">▼</span>
                        </div>
                        
                        <!-- Contenu étendu -->
                        <div class="card-expanded">
                            <div class="expanded-content">
                                <div class="info-grid">
                                    <?php if($opportunity->remuneration): ?>
                                        <div class="info-item">
                                            <span class="info-label">💰 Rémunération / Budget</span>
                                            <span class="info-value"><?php echo e($opportunity->remuneration); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if($opportunity->duree): ?>
                                        <div class="info-item">
                                            <span class="info-label">⏱️ Durée</span>
                                            <span class="info-value"><?php echo e($opportunity->duree); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if($opportunity->nombre_places): ?>
                                        <div class="info-item">
                                            <span class="info-label">👥 Nombre de places</span>
                                            <span class="info-value"><?php echo e($opportunity->nombre_places); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if($opportunity->date_debut): ?>
                                        <div class="info-item">
                                            <span class="info-label">📅 Date de début</span>
                                            <span class="info-value"><?php echo e($opportunity->date_debut); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if($opportunity->contact_email_enrichi): ?>
                                        <div class="info-item">
                                            <span class="info-label">📧 Contact</span>
                                            <span class="info-value"><?php echo e($opportunity->contact_email_enrichi); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if($opportunity->secteurs): ?>
                                    <div class="info-item">
                                        <span class="info-label">🏢 Secteurs d'activité</span>
                                        <div class="secteurs-list">
                                            <?php $__currentLoopData = $opportunity->secteurs_array; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $secteur): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="secteur-tag"><?php echo e(str_replace('_', ' ', $secteur)); ?></span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if($opportunity->criteres_eligibilite): ?>
                                    <div class="info-item">
                                        <span class="info-label">✅ Critères d'éligibilité</span>
                                        <span class="info-value"><?php echo e($opportunity->criteres_eligibilite); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($opportunity->lien_externe): ?>
                                <a href="<?php echo e($opportunity->lien_externe); ?>" 
                                   target="_blank" 
                                   class="external-link"
                                   onclick="event.stopPropagation()">
                                    Voir les détails officiels
                                    <span>↗</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Load More Section -->
            <div class="load-more-section">
                <div class="pagination-info">
                    Affichage de <?php echo e($opportunities->count()); ?> sur <?php echo e($opportunities->total()); ?> opportunités
                </div>
                
                <?php if($opportunities->hasMorePages()): ?>
                    <div class="load-more-container">
                        <button id="loadMoreBtn" class="load-more-btn" data-next-page="<?php echo e($opportunities->currentPage() + 1); ?>">
                            <span class="btn-text">Voir plus d'opportunités</span>
                            <span class="btn-loading" style="display: none;">
                                <i data-lucide="loader-2" style="width: 20px; height: 20px; animation: spin 1s linear infinite;"></i>
                                Chargement...
                            </span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <div class="no-results-icon">🔍</div>
                <h3>Aucune opportunité trouvée</h3>
                <p>Essayez de modifier vos critères de recherche ou supprimez les filtres pour voir plus d'opportunités.</p>
                <?php if($search || $selectedType !== 'tous' || $selectedInstitutionType !== 'tous'): ?>
                    <a href="<?php echo e(route('opportunites.index')); ?>" class="external-link" style="margin-top: 1rem;">
                        Voir toutes les opportunités
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleCard(cardElement) {
    // Fermer toutes les autres cartes
    const allCards = document.querySelectorAll('.opportunity-card');
    allCards.forEach(card => {
        if (card !== cardElement) {
            card.classList.remove('expanded');
        }
    });
    
    // Toggle la carte cliquée
    cardElement.classList.toggle('expanded');
}

// Smooth scroll pour les tags
document.addEventListener('DOMContentLoaded', function() {
    const tagsContainers = document.querySelectorAll('.tags-container');
    
    tagsContainers.forEach(container => {
        const activeTag = container.querySelector('.tag-pill.active');
        
        if (activeTag) {
            // Scroll vers le tag actif
            const containerRect = container.getBoundingClientRect();
            const tagRect = activeTag.getBoundingClientRect();
            const scrollPosition = tagRect.left - containerRect.left - (containerRect.width / 2) + (tagRect.width / 2);
            
            container.scrollTo({
                left: container.scrollLeft + scrollPosition,
                behavior: 'smooth'
            });
        }
    });
});

// Animation d'apparition des cartes
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.opportunity-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 50);
    });
});

// Load More Functionality
document.addEventListener('DOMContentLoaded', function() {
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const nextPage = this.getAttribute('data-next-page');
            const btnText = this.querySelector('.btn-text');
            const btnLoading = this.querySelector('.btn-loading');
            
            // Désactiver le bouton et afficher le loading
            this.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = 'flex';
            
            // Construire l'URL avec les paramètres existants
            const url = new URL(window.location);
            url.searchParams.set('page', nextPage);
            
            fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.html) {
                    // Ajouter les nouvelles cartes
                    const grid = document.getElementById('opportunitiesGrid');
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    
                    const newCards = tempDiv.querySelectorAll('.opportunity-card');
                    newCards.forEach((card, index) => {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        grid.appendChild(card);
                        
                        // Animation d'apparition
                        setTimeout(() => {
                            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, index * 50);
                    });
                    
                    // Mettre à jour les informations
                    const paginationInfo = document.querySelector('.pagination-info');
                    if (paginationInfo && data.count && data.total) {
                        paginationInfo.textContent = `Affichage de ${data.count} sur ${data.total} opportunités`;
                    }
                    
                    // Gérer le bouton pour la page suivante
                    if (data.hasMore) {
                        this.setAttribute('data-next-page', parseInt(nextPage) + 1);
                        this.disabled = false;
                        btnText.style.display = 'inline';
                        btnLoading.style.display = 'none';
                    } else {
                        // Plus de pages, cacher le bouton
                        this.parentElement.style.display = 'none';
                    }
                    
                    // Réinitialiser les icônes Lucide pour les nouvelles cartes
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement:', error);
                // Réactiver le bouton en cas d'erreur
                this.disabled = false;
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
            });
        });
    }
});

// Support pour les thèmes
document.addEventListener('DOMContentLoaded', function() {
    // Écouter les changements de thème
    window.addEventListener('theme-changed', function(e) {
        document.documentElement.setAttribute('data-theme', e.detail.theme);
    });
    
    // Initialiser les icônes Lucide
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/opportunites/index.blade.php ENDPATH**/ ?>