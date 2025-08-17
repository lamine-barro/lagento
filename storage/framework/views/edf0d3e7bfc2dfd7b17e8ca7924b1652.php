<?php $__env->startSection('title', 'Configuration du profil - Étape 3'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-white flex flex-col p-4">
    <?php if (isset($component)) { $__componentOriginal5d9cdea9ca4986a34e12a041c64e0f5f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5d9cdea9ca4986a34e12a041c64e0f5f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.onboarding.header','data' => ['currentStep' => 3]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('onboarding.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['current-step' => 3]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5d9cdea9ca4986a34e12a041c64e0f5f)): ?>
<?php $attributes = $__attributesOriginal5d9cdea9ca4986a34e12a041c64e0f5f; ?>
<?php unset($__attributesOriginal5d9cdea9ca4986a34e12a041c64e0f5f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5d9cdea9ca4986a34e12a041c64e0f5f)): ?>
<?php $component = $__componentOriginal5d9cdea9ca4986a34e12a041c64e0f5f; ?>
<?php unset($__componentOriginal5d9cdea9ca4986a34e12a041c64e0f5f); ?>
<?php endif; ?>

    <!-- Main Content -->
    <div class="flex-1 w-full max-w-4xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-medium mb-2" style="color: var(--gray-900);">
                <i data-lucide="bar-chart-3" class="w-5 h-5 mr-2 align-[-2px]"></i>
                Activité & Développement
            </h1>
            <p style="color: var(--gray-700);">Votre offre, vos cibles et votre maturité</p>
        </div>

        <form id="step3-form" method="POST" action="<?php echo e(route('onboarding.step3')); ?>" class="space-y-6">
            <?php echo csrf_field(); ?>
        
            <!-- Secteurs d'activité (max 5) -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Secteurs d'activité (max 5)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php $__currentLoopData = config('constants.SECTEURS'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all hover:border-orange-300 hover:bg-orange-50 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50" style="border-color: var(--gray-300);">
                            <input type="checkbox" name="secteurs[]" value="<?php echo e($key); ?>" class="w-4 h-4 mr-3 rounded" style="accent-color: var(--orange-primary);">
                            <span class="text-sm font-medium"><?php echo e($value); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Produits/Services (100 mots max) -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Produits/Services proposés (100 mots max)</label>
                <textarea name="produits_services" rows="3" class="input-field w-full resize-none" placeholder="Décrivez vos offres en 100 mots maximum"></textarea>
            </div>

            <!-- Clients cibles -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Clients cibles</label>
                <div class="grid grid-cols-1 gap-3">
                    <?php $__currentLoopData = config('constants.CIBLES'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all hover:border-orange-300 hover:bg-orange-50 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50" style="border-color: var(--gray-300);">
                            <input type="checkbox" name="cibles[]" value="<?php echo e($key); ?>" class="w-4 h-4 mr-3 rounded" style="accent-color: var(--orange-primary);">
                            <span class="text-sm font-medium"><?php echo e($value); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Maturité & Financement & Revenus -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Maturité du projet</label>
                    <select name="maturite" class="input-field w-full">
                        <option value="">Sélectionnez</option>
                        <?php $__currentLoopData = config('constants.STADES_MATURITE'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($value); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Stade de financement</label>
                    <select name="stade_financement" class="input-field w-full">
                        <option value="">Sélectionnez</option>
                        <?php $__currentLoopData = config('constants.STADES_FINANCEMENT'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($value); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Revenus actuels</label>
                    <select name="revenus" class="input-field w-full">
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php $__currentLoopData = config('constants.MODELES_REVENUS'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all hover:border-orange-300 hover:bg-orange-50 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50" style="border-color: var(--gray-300);">
                            <input type="checkbox" name="modeles_revenus[]" value="<?php echo e($key); ?>" class="w-4 h-4 mr-3 rounded" style="accent-color: var(--orange-primary);">
                            <span class="text-sm font-medium"><?php echo e($value); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </form>
    </div>

    <?php if (isset($component)) { $__componentOriginal4973fa7765c1d7ef7e43a98d4867113c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4973fa7765c1d7ef7e43a98d4867113c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.onboarding.footer','data' => ['nextFormId' => 'step3-form','nextLabel' => 'Suivant']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('onboarding.footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['next-form-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('step3-form'),'next-label' => 'Suivant']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4973fa7765c1d7ef7e43a98d4867113c)): ?>
<?php $attributes = $__attributesOriginal4973fa7765c1d7ef7e43a98d4867113c; ?>
<?php unset($__attributesOriginal4973fa7765c1d7ef7e43a98d4867113c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4973fa7765c1d7ef7e43a98d4867113c)): ?>
<?php $component = $__componentOriginal4973fa7765c1d7ef7e43a98d4867113c; ?>
<?php unset($__componentOriginal4973fa7765c1d7ef7e43a98d4867113c); ?>
<?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/onboarding/step3.blade.php ENDPATH**/ ?>