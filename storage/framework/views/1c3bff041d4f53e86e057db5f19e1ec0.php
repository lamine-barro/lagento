<?php $__env->startSection('title', 'Configuration du profil - Étape 4'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-white flex flex-col p-4">
    <?php if (isset($component)) { $__componentOriginal5d9cdea9ca4986a34e12a041c64e0f5f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5d9cdea9ca4986a34e12a041c64e0f5f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.onboarding.header','data' => ['currentStep' => 4]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('onboarding.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['current-step' => 4]); ?>
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
                <i data-lucide="users" class="w-5 h-5 mr-2 align-[-2px]"></i>
                Équipe & Accompagnement
            </h1>
            <p style="color: var(--gray-700);">Composition de l'équipe et besoins de soutien</p>
        </div>

        <form id="step4-form" method="POST" action="<?php echo e(route('onboarding.step4')); ?>" class="space-y-6">
            <?php echo csrf_field(); ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre de fondateurs -->
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Nombre de fondateurs *</label>
                    <div class="flex items-center border rounded-lg" style="border-color: var(--gray-300);" x-data="{ count: <?php echo e(old('founders_count', 1)); ?> }">
                        <div class="flex items-center gap-3 px-3 py-2 flex-1">
                            <i data-lucide="users" class="w-4 h-4" style="color: var(--gray-500); stroke-width: 1.25;"></i>
                            <span x-text="count" class="text-lg font-medium" style="color: var(--gray-900);"></span>
                        </div>
                        <div class="flex">
                            <button type="button" @click="if(count > 1) count--" class="p-2 hover:bg-gray-100 transition-colors border-l" style="border-color: var(--gray-300);">
                                <i data-lucide="minus" class="w-4 h-4" style="color: var(--gray-600); stroke-width: 1.25;"></i>
                            </button>
                            <button type="button" @click="count++" class="p-2 hover:bg-gray-100 transition-colors border-l" style="border-color: var(--gray-300);">
                                <i data-lucide="plus" class="w-4 h-4" style="color: var(--gray-600); stroke-width: 1.25;"></i>
                            </button>
                        </div>
                        <input type="hidden" name="founders_count" x-model="count" />
                    </div>
                </div>

                <!-- Nombre de fondatrices -->
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Nombre de femmes fondatrices *</label>
                    <div class="flex items-center border rounded-lg" style="border-color: var(--gray-300);" x-data="{ count: <?php echo e(old('female_founders_count', 0)); ?> }">
                        <div class="flex items-center gap-3 px-3 py-2 flex-1">
                            <i data-lucide="user-check" class="w-4 h-4" style="color: var(--gray-500); stroke-width: 1.25;"></i>
                            <span x-text="count" class="text-lg font-medium" style="color: var(--gray-900);"></span>
                        </div>
                        <div class="flex">
                            <button type="button" @click="if(count > 0) count--" class="p-2 hover:bg-gray-100 transition-colors border-l" style="border-color: var(--gray-300);">
                                <i data-lucide="minus" class="w-4 h-4" style="color: var(--gray-600); stroke-width: 1.25;"></i>
                            </button>
                            <button type="button" @click="count++" class="p-2 hover:bg-gray-100 transition-colors border-l" style="border-color: var(--gray-300);">
                                <i data-lucide="plus" class="w-4 h-4" style="color: var(--gray-600); stroke-width: 1.25;"></i>
                            </button>
                        </div>
                        <input type="hidden" name="female_founders_count" x-model="count" />
                    </div>
                    <p class="text-xs mt-1" style="color: var(--gray-500);">Doit être ≤ nombre de fondateurs</p>
                </div>
            </div>

            <!-- Tranches d'âge -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Tranches d'âge des fondateurs</label>
                <p class="text-sm mb-4" style="color: var(--gray-600);">Sélectionnez toutes les tranches d'âge qui s'appliquent</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <?php $__currentLoopData = config('constants.AGE_RANGES'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all hover:border-orange-300 hover:bg-orange-50 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50" style="border-color: var(--gray-300);">
                            <input type="checkbox" name="age_ranges[]" value="<?php echo e($a); ?>" class="w-4 h-4 mr-3 rounded" style="accent-color: var(--orange-primary);">
                            <span class="text-sm font-medium"><?php echo e($a); ?> ans</span>
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
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Structures d'accompagnement</label>
                <p class="text-sm mb-4" style="color: var(--gray-600);">Avez-vous déjà bénéficié de l'accompagnement d'une structure d'appui ?</p>
                <div class="grid grid-cols-1 gap-3 max-h-80 overflow-y-auto border rounded-lg p-4" style="border-color: var(--gray-200);">
                    <?php $__currentLoopData = config('constants.STRUCTURES_ACCOMPAGNEMENT'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all hover:border-orange-300 hover:bg-orange-50 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50" style="border-color: var(--gray-300);">
                            <input type="checkbox" name="support_structures[]" value="<?php echo e($s); ?>" class="w-4 h-4 mr-3 rounded" style="accent-color: var(--orange-primary);">
                            <span class="text-sm"><?php echo e($s); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Types de soutien (max 3) -->
            <div x-data="supportSelection()">
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Quels sont vos besoins prioritaires ? (5 max)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php $__currentLoopData = config('constants.TYPES_SOUTIEN'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button" 
                                @click="toggleSupport('<?php echo e($key); ?>')"
                                :class="isSelected('<?php echo e($key); ?>') ? 'bg-green-600 text-white border-green-600' : 'bg-gray-100 text-gray-700 border-gray-300 hover:border-gray-400'"
                                :disabled="!isSelected('<?php echo e($key); ?>') && selected.length >= 5"
                                class="p-4 rounded-lg border-2 transition-all text-left font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                            <?php echo e($value); ?>

                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <p class="text-sm mt-3 flex items-center gap-2" style="color: var(--gray-600);">
                    <span x-text="selected.length + '/5 besoins sélectionnés'"></span>
                    <template x-if="selected.length === 5">
                        <span class="text-orange-600 text-xs">(Maximum atteint)</span>
                    </template>
                </p>
                
                <!-- Inputs cachés pour le formulaire -->
                <template x-for="item in selected">
                    <input type="hidden" name="support_types[]" :value="item">
                </template>
            </div>

            <!-- Détails des besoins -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Détails des besoins</label>
                <textarea name="additional_info" rows="4" class="input-field w-full resize-none" placeholder="Décrivez vos besoins prioritaires..."></textarea>
            </div>
        </form>
    </div>

    <?php if (isset($component)) { $__componentOriginal4973fa7765c1d7ef7e43a98d4867113c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4973fa7765c1d7ef7e43a98d4867113c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.onboarding.footer','data' => ['nextFormId' => 'step4-form','nextLabel' => 'Finaliser','isFinal' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('onboarding.footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['next-form-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('step4-form'),'next-label' => 'Finaliser','is-final' => true]); ?>
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

<?php $__env->startPush('scripts'); ?>
<script>
function supportSelection() {
    return {
        selected: [],
        
        toggleSupport(key) {
            const index = this.selected.indexOf(key);
            if (index > -1) {
                // Désélectionner
                this.selected.splice(index, 1);
            } else {
                // Sélectionner si on n'a pas atteint le maximum
                if (this.selected.length < 5) {
                    this.selected.push(key);
                }
            }
        },
        
        isSelected(key) {
            return this.selected.includes(key);
        }
    }
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/onboarding/step4.blade.php ENDPATH**/ ?>