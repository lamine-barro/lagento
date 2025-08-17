<?php $__env->startSection('title', 'Configuration du profil - Étape 4'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-white flex flex-col p-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <button onclick="history.back()" class="btn btn-ghost p-2">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </button>
        <div class="text-center">
            <div class="text-sm font-medium" style="color: var(--orange-primary);">Étape 4 sur 4</div>
        </div>
        <div class="w-10"></div>
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
                <i data-lucide="users" class="w-5 h-5 mr-2 align-[-2px]"></i>
                Équipe & Accompagnement
            </h1>
            <p style="color: var(--gray-700);">Composition de l'équipe et besoins de soutien</p>
        </div>

        <form method="POST" action="<?php echo e(route('onboarding.step4')); ?>" class="space-y-6">
            <?php echo csrf_field(); ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Nombre de fondateurs *</label>
                    <input type="number" min="1" step="1" name="founders_count" value="<?php echo e(old('founders_count', 1)); ?>" class="input-field w-full" required />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Nombre de fondatrices *</label>
                    <input type="number" min="0" step="1" name="female_founders_count" value="<?php echo e(old('female_founders_count', 0)); ?>" class="input-field w-full" required />
                    <p class="text-xs mt-1" style="color: var(--gray-500);">Doit être ≤ nombre de fondateurs</p>
                </div>
            </div>

            <!-- Tranches d'âge -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Tranches d'âge des fondateurs</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <?php $__currentLoopData = config('constants.AGE_RANGES'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center">
                            <input type="checkbox" name="age_ranges[]" value="<?php echo e($a); ?>" class="w-4 h-4 mr-3" style="accent-color: var(--orange-primary);">
                            <span><?php echo e($a); ?> ans</span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Localisation des fondateurs</label>
                    <select name="founders_location" class="input-field w-full">
                        <option value="">Sélectionnez</option>
                        <?php $__currentLoopData = config('constants.LOCALISATION_FONDATEURS'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Taille totale de l'équipe</label>
                    <select name="team_size" class="input-field w-full">
                        <option value="">Sélectionnez</option>
                        <?php $__currentLoopData = config('constants.TEAM_SIZES'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($size); ?>"><?php echo e($size); ?> personnes</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <!-- Structures d'accompagnement -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Structures d'accompagnement existants</label>
                <div class="space-y-2">
                    <?php $__currentLoopData = config('constants.STRUCTURES_ACCOMPAGNEMENT'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center">
                            <input type="checkbox" name="support_structures[]" value="<?php echo e($s); ?>" class="w-4 h-4 mr-3" style="accent-color: var(--orange-primary);">
                            <span><?php echo e($s); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Types de soutien (max 3) -->
            <div x-data="{ selected: [] }">
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Types de soutien nécessaires (max 3)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <?php $__currentLoopData = config('constants.TYPES_SOUTIEN'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center">
                            <input type="checkbox" name="support_types[]" value="<?php echo e($key); ?>" class="w-4 h-4 mr-3" style="accent-color: var(--orange-primary);" @change="(e)=>{ if(e.target.checked && selected.length>=3){ e.target.checked=false; } else { selected = Array.from(document.querySelectorAll('input[name=\\'support_types[]\\']:checked')).map(i=>i.value) } }">
                            <span><?php echo e($value); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <p class="text-xs mt-1" style="color: var(--gray-500);" x-text="selected.length + ' / 3 sélectionnés'"></p>
            </div>

            <!-- Détails des besoins -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Détails des besoins</label>
                <textarea name="additional_info" rows="4" class="input-field w-full resize-none" placeholder="Décrivez vos besoins prioritaires..."></textarea>
            </div>
        </form>
    </div>

    <!-- Footer Navigation -->
    <div class="flex justify-between items-center mt-8">
        <button onclick="history.back()" class="btn btn-ghost">Retour</button>
        <button type="submit" class="btn btn-primary" onclick="document.querySelector('form').submit()">Finaliser</button>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/onboarding/step4.blade.php ENDPATH**/ ?>