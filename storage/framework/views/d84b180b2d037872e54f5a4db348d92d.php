<?php $__env->startSection('title', 'Diagnostic Entreprise'); ?>
<?php $__env->startSection('page_title', 'Diagnostic Entreprise Gratuit - Analysez votre Startup avec l\'IA | LAgentO'); ?>
<?php $__env->startSection('seo_title', 'Diagnostic Entreprise Gratuit - Analysez votre Startup avec l\'IA | LAgentO'); ?>
<?php $__env->startSection('meta_description', 'Obtenez un diagnostic complet et gratuit de votre entreprise avec l\'intelligence artificielle. Analyse des forces, faiblesses, opportunités de financement et conseils personnalisés pour entrepreneurs ivoiriens.'); ?>
<?php $__env->startSection('meta_keywords', 'diagnostic entreprise gratuit, analyse startup, conseil business CI, diagnostic IA, évaluation entreprise côte ivoire, audit business abidjan'); ?>
<?php $__env->startSection('canonical_url', route('diagnostic')); ?>
<?php $__env->startSection('og_title', 'Diagnostic Entreprise IA Gratuit - LAgentO Côte d\'Ivoire'); ?>
<?php $__env->startSection('og_description', 'Diagnostic IA complet de votre entreprise : forces, faiblesses, opportunités de financement et plan d\'action personnalisé. Gratuit pour entrepreneurs ivoiriens.'); ?>
<?php $__env->startSection('schema_org'); ?>

@__raw_block_0__{{ url('/') }}"
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

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
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
        
        fetch('<?php echo e(route("diagnostic.run")); ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
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
            <p class="text-secondary text-sm"><?php echo e(isset($analytics) ? 'MAJ: ' . ($analytics->metadata['derniere_maj'] ?? 'N/A') : 'Vue d\'ensemble entrepreneuriale'); ?></p>
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
                href="<?php echo e(route('onboarding.step1')); ?>"
                class="p-3 rounded-md border transition-colors flex items-center justify-center text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-800"
                style="border-color: var(--gray-300); color: var(--gray-700);"
                title="Éditer les informations du projet"
            >
                <i data-lucide="edit" class="w-4 h-4"></i>
            </a>
            
            <a 
                href="<?php echo e(route('documents.index')); ?>"
                class="p-3 rounded-md border transition-colors flex items-center justify-center text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-800"
                style="border-color: var(--gray-300); color: var(--gray-700);"
                title="Gérer les documents"
            >
                <i data-lucide="folder-open" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
    
    <!-- Loading State pour régénération de diagnostic existant -->
    <div x-show="isGenerating && <?php echo json_encode(isset($analytics), 15, 512) ?>" x-transition class="mb-4" style="display: none;">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-center py-8">
                    <div class="text-center">
                        <div class="smooth-spin w-8 h-8 border-2 border-orange border-t-transparent rounded-full mx-auto mb-4"></div>
                        <h3 class="text-lg font-medium text-primary mb-2 shimmer-text">Régénération du diagnostic en cours</h3>
                        <p class="text-muted shimmer-text">L'IA analyse vos nouvelles données entrepreneuriales...</p>
                        
                        <!-- Progress indicator avec étapes -->
                        <div class="mt-6 space-y-3">
                            <template x-for="(step, index) in progressSteps" :key="index">
                                <div class="flex items-center justify-center gap-2 text-sm text-gray-600">
                                    <div class="w-2 h-2 rounded-full" 
                                         :class="index <= progressStep ? 'bg-orange animate-pulse' : 'bg-gray-300'"></div>
                                    <span x-text="step" 
                                          :class="index <= progressStep ? 'shimmer-text' : ''"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if(isset($analytics)): ?>
    <div class="diagnostic-cards space-y-4" x-show="!isGenerating" x-transition>
        <!-- Résumé Exécutif -->
        <div class="card">
            <div class="card-header cursor-pointer" @click="toggleSection('resume')">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="card-title flex items-center gap-2">
                            <i data-lucide="star" class="w-5 h-5 text-orange"></i>
                            Résumé exécutif
                        </h3>
                        <p class="card-description">Score : <?php echo e($analytics->executive_summary['score_progression'] ?? 0); ?>/100</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors" style="z-index: 0;">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.resume ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.resume" class="card-body">
                <div class="mb-3 p-3 rounded-lg message-principal">
                    <h4 class="font-medium text-orange mb-1">Message Principal</h4>
                    <p class="text-sm"><?php echo e($analytics->executive_summary['message_principal'] ?? 'Analyse en cours...'); ?></p>
                </div>
                
                <div class="grid lg:grid-cols-2 gap-4 mt-4">
                    <div>
                        <h4 class="font-medium mb-3">3 Actions Clés</h4>
                        <div class="space-y-4">
                            <?php $__currentLoopData = ($analytics->executive_summary['trois_actions_cles'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-start gap-2">
                                <div class="w-2 h-2 bg-orange rounded-full mt-2 flex-shrink-0"></div>
                                <span class="text-sm"><?php echo e($action); ?></span>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-3">Opportunité du Mois</h4>
                        <p class="text-sm p-3 rounded" style="background: var(--success-100); color: var(--success-700);">
                            <?php echo e($analytics->executive_summary['opportunite_du_mois'] ?? 'Aucune opportunité identifiée'); ?>

                        </p>
                        
                        <?php if(isset($analytics->executive_summary['alerte_importante'])): ?>
                        <div class="mt-4">
                            <h4 class="font-medium mb-3 flex items-center gap-2 text-orange">
                                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                Alerte Importante
                            </h4>
                            <p class="text-sm p-4 rounded-lg bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300 border border-warning-200 dark:border-warning-700">
                                <?php echo e($analytics->executive_summary['alerte_importante']); ?>

                            </p>
                        </div>
                        <?php endif; ?>
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
                        <p class="card-description">Score : <?php echo e($analytics->entrepreneur_profile['score_potentiel'] ?? 0); ?>/100 | Niveau : <?php echo e(ucfirst($analytics->entrepreneur_profile['niveau_global'] ?? 'Non défini')); ?></p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors" style="z-index: 0;">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.profil ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.profil" class="card-body">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium mb-3 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="zap" class="w-4 h-4 text-orange"></i>
                            Forces Identifiées
                        </h4>
                        <div class="space-y-3">
                            <?php $__currentLoopData = ($analytics->entrepreneur_profile['forces'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $force): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <h5 class="font-medium text-orange mb-1"><?php echo e($force['domaine'] ?? 'N/A'); ?></h5>
                                <p class="text-sm text-gray-600"><?php echo e($force['description'] ?? 'N/A'); ?></p>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-3 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="trending-up" class="w-4 h-4 text-blue-500"></i>
                            Axes de Progression
                        </h4>
                        <div class="space-y-3">
                            <?php $__currentLoopData = ($analytics->entrepreneur_profile['axes_progression'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $axe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <div class="flex items-start justify-between mb-1">
                                    <h5 class="font-medium text-gray-900"><?php echo e($axe['domaine'] ?? 'N/A'); ?></h5>
                                    <span class="text-xs badge badge-<?php echo e(($axe['impact'] ?? null) === 'immédiat' ? 'orange' : (($axe['impact'] ?? null) === 'court_terme' ? 'orange' : 'gray')); ?>"><?php echo e($axe['impact'] ?? 'N/A'); ?></span>
                                </div>
                                <p class="text-sm text-gray-600"><?php echo e($axe['action_suggeree'] ?? 'N/A'); ?></p>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                            <i data-lucide="activity" class="w-5 h-5 text-orange"></i>
                            Maturité du projet
                        </h3>
                        <p class="card-description">Santé : <?php echo e($analytics->project_diagnostic['score_sante'] ?? 0); ?>/100 | Maturité : <?php echo e(ucfirst($analytics->project_diagnostic['niveau_maturite'] ?? 'Non défini')); ?></p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors" style="z-index: 0;">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.diagnostic ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.diagnostic" class="card-body">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium mb-3 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="bar-chart-3" class="w-4 h-4 text-green-500"></i>
                            Indicateurs Clés
                        </h4>
                        <div class="space-y-3">
                            <?php if(isset($analytics->project_diagnostic['indicateurs_cles'])): ?>
                            <?php $__currentLoopData = $analytics->project_diagnostic['indicateurs_cles']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $domaine => $indicateur): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <div class="flex items-center justify-between mb-1">
                                    <h5 class="font-medium capitalize text-gray-900 dark:text-gray-100"><?php echo e($domaine); ?></h5>
                                    <span class="badge badge-<?php echo e(($indicateur['statut'] ?? null) === 'ok' ? 'success' : (($indicateur['statut'] ?? null) === 'en_cours' ? 'warning' : 'gray')); ?>"><?php echo e($indicateur['statut'] ?? 'N/A'); ?></span>
                                </div>
                                <?php if(isset($indicateur['urgence'])): ?>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Urgence : <?php echo e($indicateur['urgence']); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-3 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="arrow-right" class="w-4 h-4 text-orange"></i>
                            Prochaines Étapes
                        </h4>
                        <div class="space-y-3">
                            <?php $__currentLoopData = ($analytics->project_diagnostic['prochaines_etapes'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $etape): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <div class="flex items-start gap-2">
                                    <span class="bg-orange text-white text-xs rounded-full w-5 h-5 flex items-center justify-center flex-shrink-0 font-medium"><?php echo e($etape['priorite'] ?? '?'); ?></span>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1"><?php echo e($etape['action'] ?? 'N/A'); ?></p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">Délai : <?php echo e($etape['delai'] ?? 'N/A'); ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                            <i data-lucide="target" class="w-5 h-5 text-orange"></i>
                            Opportunités matchées
                        </h3>
                        <p class="card-description"><?php echo e($analytics->matched_opportunities['nombre_total'] ?? 0); ?> opportunités identifiées</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors" style="z-index: 0;">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.opportunites ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.opportunites" class="card-body">
                <div class="space-y-4">
                    <?php $__currentLoopData = ($analytics->matched_opportunities['top_opportunites'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opportunite): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="p-4 rounded-lg border border-gray-200 bg-white opportunity-card">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-semibold text-orange text-base"><?php echo e($opportunite['titre'] ?? 'N/A'); ?></h4>
                            <span class="badge badge-<?php echo e(($opportunite['urgence'] ?? null) === 'candidater_avant_7j' ? 'orange' : 'blue'); ?>"><?php echo e($opportunite['urgence'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="mb-2">
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e($opportunite['institution'] ?? 'N/A'); ?></p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Compatibilité : <?php echo e($opportunite['score_compatibilite'] ?? 0); ?>%</p>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-3"><?php echo e($opportunite['pourquoi_vous'] ?? 'N/A'); ?></p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-orange"><?php echo e($opportunite['montant_ou_valeur'] ?? 'N/A'); ?></span>
                            <?php if(isset($opportunite['lien'])): ?>
                            <a href="<?php echo e($opportunite['lien']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-secondary">Candidater</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        <p class="card-description"><?php echo e($analytics->market_insights['position_concurrentielle']['votre_place'] ?? 'Non définie'); ?></p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors" style="z-index: 0;">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.marche ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.marche" class="card-body">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium mb-3 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="dollar-sign" class="w-4 h-4 text-green-500"></i>
                            Taille du Marché
                        </h4>
                        <div class="space-y-3">
                            <!-- Marché Local -->
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Marché Local</h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <?php echo e($analytics->market_insights['taille_marche']['local'] ?? 'N/A'); ?>

                                </p>
                            </div>
                            
                            <!-- Potentiel -->
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Potentiel de Croissance</h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <?php echo e($analytics->market_insights['taille_marche']['potentiel'] ?? 'N/A'); ?>

                                </p>
                            </div>
                            
                            <!-- Croissance -->
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Taux de Croissance</h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <?php echo e($analytics->market_insights['taille_marche']['croissance'] ?? 'N/A'); ?>

                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-3 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="map-pin" class="w-4 h-4 text-purple-500"></i>
                            Zones d'Opportunités
                        </h4>
                        <div class="space-y-3">
                            <?php $__currentLoopData = ($analytics->market_insights['zones_opportunites'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $zone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-1"><?php echo e($zone['region'] ?? 'N/A'); ?></h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e($zone['raison'] ?? 'N/A'); ?></p>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                            <i data-lucide="shield-check" class="w-5 h-5 text-orange"></i>
                            Conformité réglementaire
                        </h3>
                        <p class="card-description">Statut : <?php echo e(ucfirst($analytics->regulations['conformite_globale'] ?? 'Non défini')); ?></p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors" style="z-index: 0;">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.regulations ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.regulations" class="card-body">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium mb-3 flex items-center gap-2 text-orange">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                            Urgent
                        </h4>
                        <div class="space-y-3">
                            <?php $__currentLoopData = ($analytics->regulations['urgent'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $urgent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-3 rounded-lg border-l-3 border-orange bg-orange-50/10 dark:bg-orange-900/5">
                                <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-1"><?php echo e($urgent['obligation'] ?? 'N/A'); ?></h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Deadline : <?php echo e($urgent['deadline'] ?? 'N/A'); ?></p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">Coût : <?php echo e($urgent['cout'] ?? 'N/A'); ?></p>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium mb-3 flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <i data-lucide="calendar" class="w-4 h-4 text-blue-500"></i>
                            À Prévoir
                        </h4>
                        <div class="space-y-3">
                            <?php $__currentLoopData = ($analytics->regulations['a_prevoir'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prevoir): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-1"><?php echo e($prevoir['obligation'] ?? 'N/A'); ?></h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Échéance : <?php echo e($prevoir['echeance'] ?? 'N/A'); ?></p>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                            <i data-lucide="users" class="w-5 h-5 text-orange"></i>
                            Partenaires suggérés
                        </h3>
                        <p class="card-description"><?php echo e($analytics->suggested_partners['nombre_matches'] ?? 0); ?> partenaires potentiels</p>
                    </div>
                    <button class="text-gray-500 hover:text-gray-700 transition-colors" style="z-index: 0;">
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="sectionsState.partenaires ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>
            
            <div x-collapse x-show="sectionsState.partenaires" class="card-body">
                <div class="space-y-4">
                    <?php $__currentLoopData = ($analytics->suggested_partners['top_partenaires'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partenaire): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="p-4 rounded-lg border border-gray-200 bg-white partner-card">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-base"><?php echo e($partenaire['nom_projet'] ?? 'N/A'); ?></h4>
                            <span class="badge badge-blue"><?php echo e($partenaire['score_pertinence'] ?? 0); ?>% match</span>
                        </div>
                        <div class="mb-2">
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e($partenaire['secteur'] ?? 'N/A'); ?></p>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e($partenaire['localisation'] ?? 'N/A'); ?></p>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-2"><?php echo e($partenaire['proposition_collaboration'] ?? 'N/A'); ?></p>
                        <span class="text-xs badge badge-<?php echo e(($partenaire['type_synergie'] ?? null) === 'strategique' ? 'orange' : 'gray'); ?>"><?php echo e($partenaire['type_synergie'] ?? 'N/A'); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="card-body text-center py-12" x-show="!isGenerating">
            <i data-lucide="lightbulb" class="w-16 h-16 mx-auto mb-4 text-gray-300"></i>
            <h3 class="text-lg font-medium text-primary mb-2">Aucun diagnostic disponible</h3>
            <p class="text-muted">Lancez votre premier diagnostic pour obtenir une analyse complète de votre profil entrepreneurial.</p>
        </div>
        
        <!-- État de génération avec shimmer amélioré -->
        <div class="card-body py-12" x-show="isGenerating" style="display: none;">
            <div class="text-center mb-8">
                <div class="smooth-spin w-8 h-8 border-2 border-orange border-t-transparent rounded-full mx-auto mb-4"></div>
                <h3 class="text-lg font-medium text-primary mb-2 shimmer-text">Diagnostic en cours de génération</h3>
                <p class="text-muted shimmer-text">L'IA analyse vos données entrepreneuriales pour la première fois...</p>
                
                <!-- Progress indicator avec étapes dynamiques -->
                <div class="mt-6 space-y-3">
                    <template x-for="(step, index) in progressSteps" :key="index">
                        <div class="flex items-center justify-center gap-2 text-sm text-gray-600">
                            <div class="w-2 h-2 rounded-full" 
                                 :class="index <= progressStep ? 'bg-orange animate-pulse' : 'bg-gray-300'"></div>
                            <span x-text="step" 
                                  :class="index <= progressStep ? 'shimmer-text' : ''"></span>
                        </div>
                    </template>
                </div>
            </div>
            
            <!-- Shimmer cards preview avec structure réaliste -->
            <div class="space-y-6">
                <!-- Résumé Exécutif Preview -->
                <div class="skeleton rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="skeleton w-6 h-6 rounded"></div>
                        <div class="skeleton skeleton-title" style="width: 200px;"></div>
                    </div>
                    <div class="space-y-2">
                        <div class="skeleton skeleton-text"></div>
                        <div class="skeleton skeleton-text" style="width: 75%;"></div>
                    </div>
                </div>
                
                <!-- Profil Entrepreneur Preview -->
                <div class="skeleton rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="skeleton w-6 h-6 rounded"></div>
                        <div class="skeleton skeleton-title" style="width: 180px;"></div>
                    </div>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <div class="skeleton h-4" style="width: 120px;"></div>
                            <div class="skeleton h-8"></div>
                            <div class="skeleton h-8"></div>
                        </div>
                        <div class="space-y-3">
                            <div class="skeleton h-4" style="width: 140px;"></div>
                            <div class="skeleton h-8"></div>
                            <div class="skeleton h-8"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Opportunités Preview -->
                <div class="skeleton rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="skeleton w-6 h-6 rounded"></div>
                        <div class="skeleton skeleton-title" style="width: 220px;"></div>
                    </div>
                    <div class="space-y-4">
                        <div class="skeleton h-16 rounded"></div>
                        <div class="skeleton h-16 rounded"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
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
                    Vous avez <strong><?php echo e(auth()->user()->getRemainingDiagnostics()); ?> diagnostic(s) restant(s)</strong> ce mois-ci.
                </p>
                
                <?php if(auth()->user()->canRunDiagnostic()): ?>
                    <p class="text-sm" style="color: var(--gray-700);">
                        Voulez-vous lancer l'analyse de votre profil entrepreneurial ?
                    </p>
                <?php else: ?>
                    <div class="p-3 rounded-lg" style="background: var(--warning-50); border: 1px solid var(--warning-200);">
                        <p class="text-sm font-medium" style="color: var(--warning-700);">
                            ⚠️ Vous avez atteint la limite mensuelle de 50 diagnostics
                        </p>
                        <p class="text-xs mt-1" style="color: var(--warning-600);">
                            Prochain reset : <?php echo e(now()->addMonth()->startOfMonth()->format('d/m/Y')); ?>

                        </p>
                    </div>
                <?php endif; ?>
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
                <?php if(auth()->user()->canRunDiagnostic()): ?>
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
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
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

/* Message Principal - bordure subtile */
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

/* Skeleton loaders - couleurs système */
.skeleton {
    background: var(--gray-100);
    border: 1px solid var(--gray-200);
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.skeleton-title {
    height: 1.25rem;
    border-radius: var(--radius-sm);
}

.skeleton-text {
    height: 1rem;
    border-radius: var(--radius-sm);
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
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
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/diagnostic.blade.php ENDPATH**/ ?>