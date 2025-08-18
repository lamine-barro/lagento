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

        <form id="step4-form" method="POST" action="<?php echo e(route('onboarding.step4.process')); ?>" class="space-y-6 mt-4">
            <?php echo csrf_field(); ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ founders: <?php echo e(old('founders_count', $projet->nombre_fondateurs ?? 1)); ?>, female: <?php echo e(old('female_founders_count', $projet->nombre_fondatrices ?? 0)); ?>, decFounders() { if (this.founders > 1) { this.founders--; if (this.female > this.founders) this.female = this.founders; } }, incFounders() { this.founders++; }, decFemale() { if (this.female > 0) this.female--; }, incFemale() { if (this.female < this.founders) this.female++; } }" x-init="if (female > founders) female = founders">
                <!-- Nombre de fondateurs -->
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Nombre de fondateurs *</label>
                    <div class="flex items-center border rounded-lg" style="border-color: var(--gray-300);">
                        <div class="flex items-center px-4 py-2 flex-1">
                            <span x-text="founders" class="text-base font-medium" style="color: var(--gray-900);"></span>
                        </div>
                        <div class="flex gap-1">
                            <button type="button" @click="decFounders()" class="px-4 py-2 transition-colors border-l text-gray-700 hover:text-orange-600 active:text-orange-700" style="border-color: var(--gray-300);">
                                <span class="text-base">−</span>
                            </button>
                            <button type="button" @click="incFounders()" class="px-4 py-2 transition-colors border-l text-gray-700 hover:text-orange-600 active:text-orange-700" style="border-color: var(--gray-300);">
                                <span class="text-base">+</span>
                            </button>
                        </div>
                        <input type="hidden" name="founders_count" x-model="founders" />
                    </div>
                </div>

                <!-- Nombre de fondatrices -->
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Nombre de femmes fondatrices *</label>
                    <div class="flex items-center border rounded-lg" style="border-color: var(--gray-300);">
                        <div class="flex items-center px-4 py-2 flex-1">
                            <span x-text="female" class="text-base font-medium" style="color: var(--gray-900);"></span>
                        </div>
                        <div class="flex gap-1">
                            <button type="button" @click="decFemale()" class="px-4 py-2 transition-colors border-l text-gray-700 hover:text-orange-600 active:text-orange-700" style="border-color: var(--gray-300);">
                                <span class="text-base">−</span>
                            </button>
                            <button type="button" @click="incFemale()" class="px-4 py-2 transition-colors border-l text-gray-700 hover:text-orange-600 active:text-orange-700" style="border-color: var(--gray-300);">
                                <span class="text-base">+</span>
                            </button>
                        </div>
                        <input type="hidden" name="female_founders_count" x-model="female" />
                    </div>
                </div>
            </div>

            <!-- Tranches d'âge -->
            <div>
                <label class="block text-sm font-medium mb-2 mt-4" style="color: var(--gray-700);">Tranches d'âge des fondateurs</label>
                <p class="text-sm mb-4" style="color: var(--gray-600);">Sélectionnez toutes les tranches d'âge qui s'appliquent</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3" x-data="checkboxLimit(5, 'age_ranges[]')" x-init="updateDisabled()" @change="updateDisabled()">
                    <?php $__currentLoopData = config('constants.AGE_RANGES'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:bg-orange-50 has-[:checked]:bg-orange-50 has-[:checked]:border-orange-500" style="border-color: var(--gray-300);">
                            <input type="checkbox" name="age_ranges[]" value="<?php echo e($a); ?>" class="w-4 h-4 rounded" style="accent-color: var(--orange);" @change="onChange($event)" <?php echo e(in_array($a, old('age_ranges', $projet->tranches_age_fondateurs ?? [])) ? 'checked' : ''); ?>>
                            <span class="text-sm font-medium"><?php echo e($a); ?> ans</span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Localisation des fondateurs</label>
                    <select name="founders_location" class="input-field w-full">
                        <option value="">Sélectionnez</option>
                        <?php $__currentLoopData = config('constants.LOCALISATION_FONDATEURS'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>" <?php echo e(old('founders_location', $projet->localisation_fondateurs ?? '') == $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">Taille totale de l'équipe</label>
                    <select name="team_size" class="input-field w-full">
                        <option value="">Sélectionnez</option>
                        <?php $__currentLoopData = config('constants.TEAM_SIZES'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($size); ?>" <?php echo e(old('team_size', $projet->taille_equipe ?? '') == $size ? 'selected' : ''); ?>><?php echo e($size); ?> personnes</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <!-- Structures d'accompagnement -->
            <div>
                <label class="block text-sm font-medium mb-2 mt-4" style="color: var(--gray-700);">Structures d'accompagnement (max 5)</label>
                <p class="text-sm mb-4" style="color: var(--gray-600);">Avez-vous déjà bénéficié de l'accompagnement d'une structure d'appui ?</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3" x-data="checkboxLimit(5, 'support_structures[]')" x-init="updateDisabled()" @change="updateDisabled()">
                    <?php $__currentLoopData = config('constants.STRUCTURES_ACCOMPAGNEMENT'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:bg-orange-50 has-[:checked]:bg-orange-50 has-[:checked]:border-orange-500" style="border-color: var(--gray-300);">
                            <input type="checkbox" name="support_structures[]" value="<?php echo e($s); ?>" class="w-4 h-4 rounded" style="accent-color: var(--orange);" <?php echo e(in_array($s, old('support_structures', $projet->structures_accompagnement ?? [])) ? 'checked' : ''); ?>>
                            <span class="text-sm font-medium"><?php echo e($s); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Types de soutien (max 5) -->
            <div>
                <label class="block text-sm font-medium mb-2 mt-4" style="color: var(--gray-700);">Quels sont vos besoins prioritaires ? (5 max)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3" x-data="checkboxLimit(5, 'support_types[]')" x-init="updateDisabled()" @change="updateDisabled()">
                    <?php $__currentLoopData = config('constants.TYPES_SOUTIEN'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:bg-orange-50 has-[:checked]:bg-orange-50 has-[:checked]:border-orange-500" style="border-color: var(--gray-300);">
                            <input type="checkbox" name="support_types[]" value="<?php echo e($key); ?>" class="w-4 h-4 rounded" style="accent-color: var(--orange);" <?php echo e(in_array($key, old('support_types', $projet->types_soutien ?? [])) ? 'checked' : ''); ?>>
                            <span class="text-sm font-medium"><?php echo e($value); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Détails des besoins -->
            <div>
                <label class="block text-sm font-medium mb-2 mt-4" style="color: var(--gray-700);">Détails des besoins</label>
                <textarea name="additional_info" rows="4" class="input-field w-full resize-none" placeholder="Décrivez vos besoins prioritaires..." maxlength="800"><?php echo e(old('additional_info', $projet->details_besoins ?? '')); ?></textarea>
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
    
    <!-- Debug: Bouton temporaire de contournement -->
    <div class="text-center mt-4">
        <a href="<?php echo e(route('diagnostic')); ?>" class="text-sm text-gray-500 underline">
            [DEBUG] Aller directement au diagnostic
        </a>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function checkboxLimit(max, nameAttr) {
    return {
        max: max,
        nameAttr: nameAttr,
        init() {
            this.updateDisabled();
        },
        updateDisabled() {
            const inputs = this.$el.querySelectorAll(`input[type="checkbox"][name="${this.nameAttr}"]`);
            const checkedCount = Array.from(inputs).filter(i => i.checked).length;
            const shouldDisableOthers = checkedCount >= this.max;
            inputs.forEach(input => {
                if (!input.checked) {
                    input.disabled = shouldDisableOthers;
                } else {
                    input.disabled = false;
                }
            });
        },
        onChange() {
            this.updateDisabled();
        }
    }
}
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