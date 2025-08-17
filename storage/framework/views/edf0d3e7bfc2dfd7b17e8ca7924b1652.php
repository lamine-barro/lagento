<?php $__env->startSection('title', 'Configuration du profil - Étape 3'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-white flex flex-col p-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <button onclick="history.back()" class="btn btn-ghost p-2">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </button>
        <div class="text-center">
            <div class="text-sm font-medium" style="color: var(--orange-primary);">Étape 3 sur 3</div>
        </div>
        <div class="w-10"></div> <!-- Spacer -->
    </div>

    <!-- Progress Bar -->
    <div class="mb-8">
        <div class="h-2 rounded-full" style="background: var(--gray-100);">
            <div class="h-2 rounded-full transition-all duration-500" style="background: var(--orange-primary); width: 100%;"></div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 w-full" style="max-width: 720px; margin-left: auto; margin-right: auto;">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-medium mb-2" style="color: var(--gray-900);">
                <i data-lucide="bar-chart-3" class="w-5 h-5 mr-2 align-[-2px]"></i>
                Activité & Développement
            </h1>
            <p style="color: var(--gray-700);">Votre offre, vos cibles et votre maturité</p>
        </div>

        <form method="POST" action="<?php echo e(route('onboarding.step3')); ?>" class="space-y-6">
            <?php echo csrf_field(); ?>
        
            <!-- Secteurs d'activité (max 5) -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Secteurs d'activité (max 5)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <?php $__currentLoopData = config('constants.SECTEURS'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center">
                            <input type="checkbox" name="business_sector_multi[]" value="<?php echo e($key); ?>" class="w-4 h-4 mr-3" style="accent-color: var(--orange-primary);">
                            <span><?php echo e($value); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Produits/Services (100 mots max) -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Produits/Services proposés (100 mots max)</label>
                <textarea name="products" rows="3" class="input-field w-full resize-none" placeholder="Décrivez vos offres en 100 mots maximum"></textarea>
            </div>

            <!-- Clients cibles -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Clients cibles</label>
                <div class="space-y-2">
                    <?php $__currentLoopData = config('constants.CIBLES'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center">
                            <input type="checkbox" name="target_clients[]" value="<?php echo e($key); ?>" class="w-4 h-4 mr-3" style="accent-color: var(--orange-primary);">
                            <span><?php echo e($value); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Maturité & Financement & Revenus -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Maturité du projet</label>
                    <select name="business_stage" class="input-field w-full">
                        <option value="">Sélectionnez</option>
                        <?php $__currentLoopData = config('constants.STADES_MATURITE'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($value); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Stade de financement</label>
                    <select name="funding_stage" class="input-field w-full">
                        <option value="">Sélectionnez</option>
                        <?php $__currentLoopData = config('constants.STADES_FINANCEMENT'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($value); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Revenus actuels</label>
                    <select name="monthly_revenue" class="input-field w-full">
                        <option value="">Sélectionnez</option>
                        <?php $__currentLoopData = config('constants.TRANCHES_REVENUS'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($value); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <!-- Modèles de revenus (max 5) -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Modèles de revenus (max 5)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <?php $__currentLoopData = config('constants.MODELES_REVENUS'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center">
                            <input type="checkbox" name="revenue_models[]" value="<?php echo e($key); ?>" class="w-4 h-4 mr-3" style="accent-color: var(--orange-primary);">
                            <span><?php echo e($value); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Footer Navigation -->
    <div class="flex justify-between items-center mt-8">
        <button onclick="history.back()" class="btn btn-ghost">
            Retour
        </button>
        <button 
            type="submit" 
            class="btn btn-primary"
            onclick="document.querySelector('form').submit()"
            x-bind:disabled="supportTypes.length === 0 || challenges.length === 0"
        >
            Finaliser mon profil
        </button>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/onboarding/step3.blade.php ENDPATH**/ ?>