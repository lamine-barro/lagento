<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="container max-w-7xl mx-auto section">
    <!-- En-tête -->
    <div class="mb-6">
        <h1 class="text-primary mb-2">Dashboard</h1>
        <p class="text-secondary">Vue d'ensemble de votre activité entrepreneuriale</p>
    </div>
    
    <!-- Grille des cartes -->
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        
        <!-- Opportunités matchées -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Opportunités</h3>
                <p class="card-description"><?php echo e(isset($opportunities) ? $opportunities->count() : 0); ?> disponibles</p>
            </div>
            
            <div class="card-body">
                <?php if(isset($opportunities) && $opportunities->count() > 0): ?>
                    <div class="space-y-3">
                        <?php $__currentLoopData = $opportunities->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opportunity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-start gap-3 p-3 bg-gray-100 rounded">
                            <div class="w-2 h-2 bg-orange rounded-full mt-2 flex-shrink-0"></div>
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-primary"><?php echo e($opportunity->titre); ?></h4>
                                <p class="text-xs text-muted mt-1"><?php echo e(Str::limit($opportunity->description, 60)); ?></p>
                                <?php if($opportunity->deadline): ?>
                                    <span class="badge badge-gray mt-2"><?php echo e($opportunity->deadline); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        
                        <?php if($opportunities->count() > 3): ?>
                        <p class="text-xs text-center text-muted">+<?php echo e($opportunities->count() - 3); ?> autres opportunités</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-6">
                        <svg class="w-8 h-8 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <p class="text-muted">Aucune opportunité disponible</p>
                    </div>
                <?php endif; ?>
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
                        <div class="text-2xl font-semibold text-orange"><?php echo e(isset($userAnalytics) ? $userAnalytics->interactions_count ?? 0 : 0); ?></div>
                        <div class="text-xs text-muted">Interactions</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-semibold text-orange"><?php echo e(isset($userAnalytics) ? $userAnalytics->projets_count ?? 0 : 0); ?></div>
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
                <?php if(isset($recentProjects) && $recentProjects->count() > 0): ?>
                    <div class="space-y-3">
                        <?php $__currentLoopData = $recentProjects->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 bg-success rounded-full flex-shrink-0"></div>
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-primary"><?php echo e($project->nom); ?></h4>
                                <p class="text-xs text-muted"><?php echo e($project->secteur ?? 'Secteur non défini'); ?></p>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-6">
                        <svg class="w-8 h-8 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-muted">Aucun projet créé</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if(Route::has('projets.index')): ?>
            <div class="card-footer">
                <a href="<?php echo e(route('projets.index')); ?>" class="btn btn-secondary btn-sm w-full">
                    Voir tous les projets
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Conversations récentes -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Conversations</h3>
                <p class="card-description"><?php echo e(isset($userAnalytics) ? $userAnalytics->conversations_count ?? 0 : 0); ?> conversations</p>
            </div>
            
            <div class="card-body">
                <?php if(isset($recentConversations) && $recentConversations->count() > 0): ?>
                    <div class="space-y-3">
                        <?php $__currentLoopData = $recentConversations->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conversation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-orange rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-xs text-white font-medium">IA</span>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-primary"><?php echo e($conversation->titre); ?></h4>
                                <p class="text-xs text-muted"><?php echo e($conversation->updated_at->diffForHumans()); ?></p>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-6">
                        <svg class="w-8 h-8 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <p class="text-muted">Aucune conversation</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if(Route::has('conversations.index')): ?>
            <div class="card-footer">
                <a href="<?php echo e(route('conversations.index')); ?>" class="btn btn-secondary btn-sm w-full">
                    Voir toutes les conversations
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Actions rapides -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Actions rapides</h3>
                <p class="card-description">Commencer quelque chose de nouveau</p>
            </div>
            
            <div class="card-body">
                <div class="space-y-3">
                    <a href="<?php echo e(route('chat')); ?>" class="btn btn-primary w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Nouvelle conversation
                    </a>
                    
                    <?php if(Route::has('projets.create')): ?>
                    <a href="<?php echo e(route('projets.create')); ?>" class="btn btn-secondary w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nouveau projet
                    </a>
                    <?php endif; ?>
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
        <div class="card md:col-span-2 lg:col-span-3 xl:col-span-4">
            <div class="card-header">
                <h3 class="card-title">Statistiques détaillées</h3>
                <p class="card-description">Vue d'ensemble de votre activité</p>
            </div>
            
            <div class="card-body">
                <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-semibold text-orange mb-2"><?php echo e(isset($userAnalytics) ? $userAnalytics->interactions_count ?? 0 : 0); ?></div>
                        <div class="text-sm text-muted">Interactions avec l'IA</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-semibold text-orange mb-2"><?php echo e(isset($userAnalytics) ? $userAnalytics->projets_count ?? 0 : 0); ?></div>
                        <div class="text-sm text-muted">Projets créés</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-semibold text-orange mb-2"><?php echo e(isset($userAnalytics) ? $userAnalytics->conversations_count ?? 0 : 0); ?></div>
                        <div class="text-sm text-muted">Conversations</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-semibold text-orange mb-2"><?php echo e(isset($userAnalytics) ? $userAnalytics->documents_count ?? 0 : 0); ?></div>
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

@media (min-width: 1280px) {
  .xl\:grid-cols-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
  
  .xl\:col-span-4 {
    grid-column: span 4 / span 4;
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

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/dashboard.blade.php ENDPATH**/ ?>