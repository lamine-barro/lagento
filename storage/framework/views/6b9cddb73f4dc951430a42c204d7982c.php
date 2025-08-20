<?php $__env->startSection('seo_title', 'Annuaire des Projets Entrepreneuriaux - Côte d\'Ivoire | Agento'); ?>
<?php $__env->startSection('meta_description', 'Découvrez les projets innovants d\'entrepreneurs ivoiriens. Trouvez des partenaires, investisseurs et opportunités de collaboration dans l\'écosystème startup de Côte d\'Ivoire.'); ?>
<?php $__env->startSection('meta_keywords', 'projets startup côte ivoire, entrepreneurs ivoiriens, annuaire entreprises ci, innovation abidjan, partenaires business afrique'); ?>
<?php $__env->startSection('meta_robots', 'index, follow'); ?>
<?php $__env->startSection('og_title', 'Annuaire des Projets Entrepreneuriaux en Côte d\'Ivoire'); ?>
<?php $__env->startSection('og_description', 'Plus de <?php echo e($projets->total()); ?> projets entrepreneuriaux référencés en Côte d\'Ivoire. Connectez-vous avec l\'écosystème innovation ivoirien.'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2" style="color: var(--gray-900);">
            Annuaire des Projets
        </h1>
        <p style="color: var(--gray-700);">
            Découvrez les projets entrepreneuriaux de la Côte d'Ivoire
        </p>
    </div>

    <!-- Filtres de recherche -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Recherche textuelle -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                    Rechercher
                </label>
                <input
                    type="text"
                    name="search"
                    value="<?php echo e(request('search')); ?>"
                    placeholder="Nom du projet, description..."
                    class="input-field w-full"
                />
            </div>

            <!-- Région -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                    Région
                </label>
                <select name="region" class="input-field w-full">
                    <option value="">Toutes les régions</option>
                    <?php $__currentLoopData = config('constants.REGIONS'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $region => $coords): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($region); ?>" <?php echo e(request('region') == $region ? 'selected' : ''); ?>>
                            <?php echo e($region); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- Secteur -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                    Secteur
                </label>
                <select name="secteur" class="input-field w-full">
                    <option value="">Tous les secteurs</option>
                    <?php $__currentLoopData = config('constants.SECTEURS'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($key); ?>" <?php echo e(request('secteur') == $key ? 'selected' : ''); ?>>
                            <?php echo e($value); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- Maturité -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                    Maturité
                </label>
                <select name="maturite" class="input-field w-full">
                    <option value="">Tous les stades</option>
                    <?php $__currentLoopData = config('constants.STADES_MATURITE'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($key); ?>" <?php echo e(request('maturite') == $key ? 'selected' : ''); ?>>
                            <?php echo e($value); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- Boutons -->
            <div class="md:col-span-4 flex gap-3">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                    Rechercher
                </button>
                <a href="<?php echo e(route('projets.index')); ?>" class="btn btn-ghost">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <!-- Résultats -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <p style="color: var(--gray-700);">
                <?php echo e($projets->total()); ?> projet(s) trouvé(s)
            </p>
            <?php if(request()->hasAny(['search', 'region', 'secteur', 'maturite'])): ?>
                <p class="text-sm mt-1" style="color: var(--gray-500);">
                    <?php if(request('search')): ?>
                        Recherche : "<?php echo e(request('search')); ?>"
                    <?php endif; ?>
                    <?php if(request('region')): ?>
                        • Région : <?php echo e(request('region')); ?>

                    <?php endif; ?>
                    <?php if(request('secteur')): ?>
                        • Secteur : <?php echo e(config('constants.SECTEURS')[request('secteur')] ?? request('secteur')); ?>

                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
        <a href="<?php echo e(route('projets.create')); ?>" class="btn btn-primary">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Ajouter mon projet
        </a>
    </div>

    <!-- Grille des projets -->
    <?php if($projets->count() > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            <?php $__currentLoopData = $projets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $projet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                    <!-- Header avec logo -->
                    <div class="p-6 border-b">
                        <div class="flex items-start gap-4">
                            <?php if($projet->logo_url): ?>
                                <img 
                                    src="<?php echo e(Storage::url($projet->logo_url)); ?>" 
                                    alt="Logo <?php echo e($projet->nom_projet); ?>"
                                    class="w-16 h-16 rounded-lg object-cover"
                                />
                            <?php else: ?>
                                <div class="w-16 h-16 rounded-lg flex items-center justify-center text-white text-xl font-bold" style="background: var(--orange-primary);">
                                    <?php echo e(strtoupper(substr($projet->nom_projet, 0, 1))); ?>

                                </div>
                            <?php endif; ?>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold mb-1" style="color: var(--gray-900);">
                                    <?php echo e($projet->nom_projet); ?>

                                </h3>
                                <p class="text-sm" style="color: var(--gray-600);">
                                    <?php echo e($projet->region); ?>

                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu -->
                    <div class="p-6">
                        <p class="text-sm mb-4 line-clamp-3" style="color: var(--gray-700);">
                            <?php echo e(Str::limit($projet->description, 120)); ?>

                        </p>

                        <!-- Tags secteurs -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            <?php $__currentLoopData = array_slice($projet->secteurs_labels, 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $secteur): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo e($secteur); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if(count($projet->secteurs_labels) > 2): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    +<?php echo e(count($projet->secteurs_labels) - 2); ?>

                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Badges status -->
                        <div class="flex gap-2 mb-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <?php echo e($projet->getMaturiteLabel()); ?>

                            </span>
                            <?php if($projet->is_verified): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white" style="background: var(--orange-primary);">
                                    <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                    Vérifié
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <a 
                                href="<?php echo e(route('projets.show', $projet)); ?>" 
                                class="btn btn-primary flex-1 text-center"
                            >
                                Voir le projet
                            </a>
                            <?php if($projet->email || $projet->telephone): ?>
                                <button class="btn btn-ghost px-3" title="Contact disponible">
                                    <i data-lucide="mail" class="w-4 h-4"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            <?php echo e($projets->appends(request()->query())->links()); ?>

        </div>
    <?php else: ?>
        <!-- Aucun résultat -->
        <div class="text-center py-12">
            <div class="text-gray-400 mb-4">
                <i data-lucide="search" class="w-16 h-16 mx-auto"></i>
            </div>
            <h3 class="text-lg font-medium mb-2" style="color: var(--gray-900);">
                Aucun projet trouvé
            </h3>
            <p class="mb-6" style="color: var(--gray-600);">
                Essayez d'ajuster vos critères de recherche
            </p>
            <a href="<?php echo e(route('projets.create')); ?>" class="btn btn-primary">
                Soyez le premier à ajouter votre projet
            </a>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/projets/index.blade.php ENDPATH**/ ?>