<?php $__empty_1 = true; $__currentLoopData = $opportunities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opportunity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="opportunity-card bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-orange-300 dark:hover:border-orange-500 hover:shadow-md transition-all duration-200 cursor-pointer" data-id="<?php echo e($opportunity->id); ?>">
        <div class="p-6">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-orange-500"></i>
                    <span class="text-sm text-gray-600 dark:text-gray-300 font-medium"><?php echo e($opportunity->institution); ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400 border border-green-200 dark:border-green-800">
                        <i data-lucide="circle-check" class="w-3 h-3"></i>
                        <?php echo e($opportunity->statut); ?>

                    </span>
                </div>
            </div>

            <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-3 line-clamp-2">
                <?php echo e($opportunity->titre); ?>

            </h3>

            <p class="text-gray-600 dark:text-gray-300 mb-4 line-clamp-3">
                <?php echo e(Str::limit($opportunity->description, 150)); ?>

            </p>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                    <div class="flex items-center gap-1">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                        <span><?php echo e($opportunity->pays); ?></span>
                    </div>
                    <?php if($opportunity->date_limite_candidature && $opportunity->date_limite_candidature !== 'Continu'): ?>
                        <div class="flex items-center gap-1">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                            <span><?php echo e($opportunity->date_limite_candidature); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                    <?php
                        $typeIcon = match($opportunity->type) {
                            'Financement' => 'banknote',
                            'Formation' => 'graduation-cap',
                            'Stage' => 'briefcase',
                            'Programme' => 'layers',
                            'Concours' => 'award',
                            'Emploi' => 'user-check',
                            'Bourse' => 'scholar',
                            default => 'star'
                        };
                    ?>
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                        <i data-lucide="<?php echo e($typeIcon); ?>" class="w-3 h-3"></i>
                        <?php echo e($opportunity->type); ?>

                    </span>
                </div>
            </div>
        </div>

        <!-- Contenu étendu (caché par défaut) -->
        <div class="expanded-content hidden border-t border-gray-200 dark:border-gray-700">
            <div class="p-6 space-y-4">
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Description complète</h4>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed"><?php echo e($opportunity->description); ?></p>
                </div>

                <?php if($opportunity->criteres_eligibilite): ?>
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Critères d'éligibilité</h4>
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed"><?php echo e($opportunity->criteres_eligibilite); ?></p>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php if($opportunity->duree): ?>
                        <div class="flex items-center gap-2">
                            <i data-lucide="clock" class="w-4 h-4 text-gray-500"></i>
                            <span class="text-sm"><strong>Durée:</strong> <?php echo e($opportunity->duree); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if($opportunity->remuneration): ?>
                        <div class="flex items-center gap-2">
                            <i data-lucide="dollar-sign" class="w-4 h-4 text-gray-500"></i>
                            <span class="text-sm"><strong>Rémunération:</strong> <?php echo e($opportunity->remuneration); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if($opportunity->nombre_places): ?>
                        <div class="flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4 text-gray-500"></i>
                            <span class="text-sm"><strong>Places:</strong> <?php echo e($opportunity->nombre_places); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if($opportunity->date_debut && $opportunity->date_debut !== 'Continu'): ?>
                        <div class="flex items-center gap-2">
                            <i data-lucide="calendar-plus" class="w-4 h-4 text-gray-500"></i>
                            <span class="text-sm"><strong>Début:</strong> <?php echo e($opportunity->date_debut); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if($opportunity->secteurs_array && count($opportunity->secteurs_array) > 0): ?>
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Secteurs</h4>
                        <div class="flex flex-wrap gap-2">
                            <?php $__currentLoopData = $opportunity->secteurs_array; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $secteur): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    <?php echo e(trim($secteur)); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($opportunity->regions_ciblees_array && count($opportunity->regions_ciblees_array) > 0): ?>
                    <div>
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Régions ciblées</h4>
                        <div class="flex flex-wrap gap-2">
                            <?php $__currentLoopData = $opportunity->regions_ciblees_array; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $region): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    <?php echo e(trim($region)); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($opportunity->contact_email_enrichi): ?>
                    <div class="flex items-center gap-2">
                        <i data-lucide="mail" class="w-4 h-4 text-gray-500"></i>
                        <span class="text-sm"><strong>Contact:</strong> <?php echo e($opportunity->contact_email_enrichi); ?></span>
                    </div>
                <?php endif; ?>

                <?php if($opportunity->lien_externe): ?>
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="<?php echo e($opportunity->lien_externe); ?>" target="_blank" rel="noopener noreferrer" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium transition-colors duration-200">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                            Voir l'opportunité
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="col-span-full text-center py-12">
        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
            <i data-lucide="search-x" class="w-8 h-8 text-gray-400"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Aucune opportunité trouvée</h3>
        <p class="text-gray-500 dark:text-gray-400">Essayez de modifier vos filtres ou votre recherche.</p>
    </div>
<?php endif; ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/opportunites/partials/cards.blade.php ENDPATH**/ ?>