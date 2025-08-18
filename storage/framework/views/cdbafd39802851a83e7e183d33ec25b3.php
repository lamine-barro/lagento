<?php $__env->startSection('seo_title', $projet->nom_projet . ' - ' . $projet->getMaturiteLabel() . ' | Projet Entrepreneurial Côte d\'Ivoire'); ?>
<?php $__env->startSection('meta_description', 'Découvrez ' . $projet->nom_projet . ' : ' . Str::limit($projet->description, 150) . ' Secteur : ' . implode(', ', array_slice($projet->secteurs_labels, 0, 2)) . '. Région : ' . $projet->region . '.'); ?>
<?php $__env->startSection('meta_keywords', implode(', ', $projet->secteurs_labels) . ', ' . $projet->region . ', startup côte ivoire, entrepreneur ivoirien'); ?>
<?php $__env->startSection('meta_robots', $projet->is_public ? 'index, follow' : 'noindex, nofollow'); ?>
<?php $__env->startSection('og_title', $projet->nom_projet . ' - Projet Entrepreneurial en Côte d\'Ivoire'); ?>
<?php $__env->startSection('og_description', $projet->description); ?>
<?php $__env->startSection('og_image', $projet->logo_url ? Storage::url($projet->logo_url) : asset('images/lagento-project-default.jpg')); ?>
<?php $__env->startSection('og_type', 'article'); ?>
<?php $__env->startSection('canonical_url', route('projets.show', $projet)); ?>

<?php $__env->startSection('schema_org', json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => $projet->nom_projet,
    'legalName' => $projet->raison_sociale,
    'description' => $projet->description,
    'url' => $projet->site_web,
    'logo' => $projet->logo_url ? Storage::url($projet->logo_url) : null,
    'foundingDate' => $projet->annee_creation,
    'email' => $projet->email,
    'telephone' => $projet->telephone,
    'address' => [
        '@type' => 'PostalAddress',
        'addressRegion' => $projet->region,
        'addressCountry' => 'CI'
    ],
    'sameAs' => array_filter(array_values($projet->reseaux_sociaux ?? [])),
    'knowsAbout' => $projet->secteurs_labels,
    'founder' => [
        '@type' => 'Person',
        'name' => $projet->user->name
    ],
    'employee' => [
        '@type' => 'QuantitativeValue',
        'value' => $projet->taille_equipe
    ],
    'makesOffer' => [
        '@type' => 'Offer',
        'itemOffered' => [
            '@type' => 'Service',
            'name' => implode(', ', $projet->produits_services ?? []),
            'category' => implode(', ', $projet->secteurs_labels)
        ]
    ]
])); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Header avec actions -->
    <div class="flex justify-between items-start mb-8">
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('projets.index')); ?>" class="btn btn-ghost p-2">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold mb-2" style="color: var(--gray-900);">
                    <?php echo e($projet->nom_projet); ?>

                </h1>
                <?php if($projet->raison_sociale): ?>
                    <p class="text-lg" style="color: var(--gray-600);">
                        <?php echo e($projet->raison_sociale); ?>

                    </p>
                <?php endif; ?>
            </div>
        </div>

        <?php if(Auth::id() === $projet->user_id): ?>
            <div class="flex gap-2">
                <a href="<?php echo e(route('projets.edit', $projet)); ?>" class="btn btn-primary">
                    <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                    Modifier
                </a>
                <form method="POST" action="<?php echo e(route('projets.toggle-visibility', $projet)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-ghost">
                        <?php if($projet->is_public): ?>
                            <i data-lucide="eye-off" class="w-4 h-4 mr-2"></i>
                            Rendre privé
                        <?php else: ?>
                            <i data-lucide="eye" class="w-4 h-4 mr-2"></i>
                            Rendre public
                        <?php endif; ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Contenu principal -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Identité et Description -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-start gap-6 mb-6">
                    <?php if($projet->logo_url): ?>
                        <img 
                            src="<?php echo e(Storage::url($projet->logo_url)); ?>" 
                            alt="Logo <?php echo e($projet->nom_projet); ?>"
                            class="w-24 h-24 rounded-lg object-cover"
                        />
                    <?php else: ?>
                        <div class="w-24 h-24 rounded-lg flex items-center justify-center text-white text-2xl font-bold" style="background: var(--orange-primary);">
                            <?php echo e(strtoupper(substr($projet->nom_projet, 0, 1))); ?>

                        </div>
                    <?php endif; ?>
                    <div class="flex-1">
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <?php echo e($projet->getMaturiteLabel()); ?>

                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <?php echo e($projet->region); ?>

                            </span>
                            <?php if($projet->is_verified): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-white" style="background: var(--orange-primary);">
                                    <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i>
                                    Vérifié
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm" style="color: var(--gray-600);">
                            <time datetime="<?php echo e($projet->last_updated_at->toISOString()); ?>">
                                Mis à jour le <?php echo e($projet->last_updated_at->format('d/m/Y')); ?>

                            </time>
                        </p>
                    </div>
                </div>

                <div class="prose max-w-none">
                    <h2 class="text-xl font-semibold mb-4" style="color: var(--gray-900);">
                        Description du projet
                    </h2>
                    <p style="color: var(--gray-700);">
                        <?php echo e($projet->description); ?>

                    </p>
                </div>
            </div>

            <!-- Activité -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold mb-4" style="color: var(--gray-900);">
                    Activité
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Secteurs -->
                    <div>
                        <h3 class="font-medium mb-3" style="color: var(--gray-700);">Secteurs d'activité</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php $__currentLoopData = $projet->secteurs_labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $secteur): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo e($secteur); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <!-- Cibles -->
                    <div>
                        <h3 class="font-medium mb-3" style="color: var(--gray-700);">Marchés cibles</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php $__currentLoopData = $projet->cibles_labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cible): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    <?php echo e($cible); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>

                <!-- Produits/Services -->
                <?php if($projet->produits_services): ?>
                    <div class="mt-6">
                        <h3 class="font-medium mb-3" style="color: var(--gray-700);">Produits & Services</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php $__currentLoopData = $projet->produits_services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <?php echo e($item); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Développement -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold mb-4" style="color: var(--gray-900);">
                    Développement & Financement
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-medium mb-2" style="color: var(--gray-700);">Stade de financement</h3>
                        <p style="color: var(--gray-900);"><?php echo e($projet->getStadeFinancementLabel()); ?></p>
                    </div>
                    
                    <div>
                        <h3 class="font-medium mb-2" style="color: var(--gray-700);">Revenus</h3>
                        <p style="color: var(--gray-900);"><?php echo e($projet->getRevenusLabel()); ?></p>
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="font-medium mb-3" style="color: var(--gray-700);">Modèles de revenus</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php $__currentLoopData = $projet->modeles_revenus_labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modele): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <?php echo e($modele); ?>

                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            <!-- Équipe -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold mb-4" style="color: var(--gray-900);">
                    Équipe
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <h3 class="font-medium mb-2" style="color: var(--gray-700);">Fondateurs</h3>
                        <p style="color: var(--gray-900);"><?php echo e($projet->getNombreFondateursLabel()); ?></p>
                    </div>
                    
                    <div>
                        <h3 class="font-medium mb-2" style="color: var(--gray-700);">Fondatrices</h3>
                        <p style="color: var(--gray-900);"><?php echo e($projet->getNombreFondatricesLabel()); ?></p>
                    </div>
                    
                    <div>
                        <h3 class="font-medium mb-2" style="color: var(--gray-700);">Taille équipe</h3>
                        <p style="color: var(--gray-900);"><?php echo e($projet->getTailleEquipeLabel()); ?></p>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-medium mb-3" style="color: var(--gray-700);">Tranches d'âge fondateurs</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php $__currentLoopData = $projet->tranches_age_fondateurs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tranche): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    <?php echo e($tranche); ?> ans
                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="font-medium mb-2" style="color: var(--gray-700);">Localisation</h3>
                        <p style="color: var(--gray-900);"><?php echo e($projet->getLocalisationFondateursLabel()); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Contact -->
            <?php if($projet->email || $projet->telephone || $projet->site_web || $projet->reseaux_sociaux): ?>
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-semibold mb-4" style="color: var(--gray-900);">
                        Contact
                    </h3>
                    
                    <div class="space-y-3">
                        <?php if($projet->email): ?>
                            <div class="flex items-center gap-3">
                                <i data-lucide="mail" class="w-4 h-4" style="color: var(--gray-500);"></i>
                                <a href="mailto:<?php echo e($projet->email); ?>" class="text-sm hover:underline" style="color: var(--orange-primary);">
                                    <?php echo e($projet->email); ?>

                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($projet->telephone): ?>
                            <div class="flex items-center gap-3">
                                <i data-lucide="phone" class="w-4 h-4" style="color: var(--gray-500);"></i>
                                <a href="tel:<?php echo e($projet->telephone); ?>" class="text-sm hover:underline" style="color: var(--orange-primary);">
                                    <?php echo e($projet->telephone); ?>

                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($projet->site_web): ?>
                            <div class="flex items-center gap-3">
                                <i data-lucide="globe" class="w-4 h-4" style="color: var(--gray-500);"></i>
                                <a href="<?php echo e($projet->site_web); ?>" target="_blank" class="text-sm hover:underline" style="color: var(--orange-primary);">
                                    Visiter le site
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if($projet->reseaux_sociaux): ?>
                        <div class="mt-6">
                            <h4 class="font-medium mb-3" style="color: var(--gray-700);">Réseaux sociaux</h4>
                            <div class="flex flex-wrap gap-2">
                                <?php $__currentLoopData = $projet->reseaux_sociaux; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reseau => $handle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($handle): ?>
                                        <a href="#" class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-100 hover:bg-gray-200">
                                            <?php echo e(ucfirst($reseau)); ?>

                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Besoins -->
            <?php if($projet->types_soutien_labels || $projet->structures_accompagnement_labels): ?>
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-semibold mb-4" style="color: var(--gray-900);">
                        Besoins d'accompagnement
                    </h3>
                    
                    <?php if($projet->types_soutien_labels): ?>
                        <div class="mb-4">
                            <h4 class="font-medium mb-3" style="color: var(--gray-700);">Types de soutien</h4>
                            <div class="space-y-2">
                                <?php $__currentLoopData = $projet->types_soutien_labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full" style="background: var(--orange-primary);"></div>
                                        <span class="text-sm" style="color: var(--gray-700);"><?php echo e($type); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($projet->structures_accompagnement_labels): ?>
                        <div>
                            <h4 class="font-medium mb-3" style="color: var(--gray-700);">Structures recherchées</h4>
                            <div class="flex flex-wrap gap-1">
                                <?php $__currentLoopData = $projet->structures_accompagnement_labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $structure): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="inline-block px-2 py-1 text-xs bg-gray-100 rounded">
                                        <?php echo e($structure); ?>

                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($projet->details_besoins): ?>
                        <div class="mt-4 pt-4 border-t">
                            <h4 class="font-medium mb-2" style="color: var(--gray-700);">Détails</h4>
                            <p class="text-sm" style="color: var(--gray-600);">
                                <?php echo e($projet->details_besoins); ?>

                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Formalisation -->
            <?php if($projet->formalise === 'oui'): ?>
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="font-semibold mb-4" style="color: var(--gray-900);">
                        Informations légales
                    </h3>
                    
                    <div class="space-y-3">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Entreprise formalisée
                            </span>
                        </div>
                        
                        <?php if($projet->annee_creation): ?>
                            <div>
                                <span class="text-sm font-medium" style="color: var(--gray-700);">Année de création:</span>
                                <span class="text-sm" style="color: var(--gray-900);"><?php echo e($projet->annee_creation); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($projet->numero_rccm): ?>
                            <div>
                                <span class="text-sm font-medium" style="color: var(--gray-700);">N° RCCM:</span>
                                <span class="text-sm" style="color: var(--gray-900);"><?php echo e($projet->numero_rccm); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/projets/show.blade.php ENDPATH**/ ?>