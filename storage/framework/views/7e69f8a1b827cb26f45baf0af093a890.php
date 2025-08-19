<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'show' => 'false',
    'title' => 'Confirmation',
    'message' => '',
    'confirmText' => 'Confirmer',
    'cancelText' => 'Annuler',
    'onConfirm' => '',
    'onCancel' => '',
    'icon' => 'alert-triangle',
    'iconColor' => 'var(--warning)',
    'iconBg' => 'var(--warning-100)',
    'danger' => false,
    'loading' => 'false',
    'loadingText' => 'Traitement...',
    'zIndex' => '99999'
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'show' => 'false',
    'title' => 'Confirmation',
    'message' => '',
    'confirmText' => 'Confirmer',
    'cancelText' => 'Annuler',
    'onConfirm' => '',
    'onCancel' => '',
    'icon' => 'alert-triangle',
    'iconColor' => 'var(--warning)',
    'iconBg' => 'var(--warning-100)',
    'danger' => false,
    'loading' => 'false',
    'loadingText' => 'Traitement...',
    'zIndex' => '99999'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    if ($danger) {
        $iconColor = 'var(--danger)';
        $iconBg = 'var(--danger-100)';
        $icon = $icon === 'alert-triangle' ? 'trash-2' : $icon;
    }
?>

<?php if (isset($component)) { $__componentOriginal56b7ba19a438470b02fb04d45bfe6840 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56b7ba19a438470b02fb04d45bfe6840 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.base','data' => ['show' => $show,'maxWidth' => 'md','zIndex' => $zIndex]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal.base'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['show' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($show),'max-width' => 'md','z-index' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($zIndex)]); ?>
    <!-- Modal Header -->
    <div class="card-header">
        <div class="flex items-center" style="gap: var(--space-3);">
            <div class="flex items-center justify-center flex-shrink-0" 
                 style="width: 40px; height: 40px; border-radius: var(--radius-full); background: <?php echo e($iconBg); ?>;">
                <i data-lucide="<?php echo e($icon); ?>" class="w-5 h-5" style="color: <?php echo e($iconColor); ?>;"></i>
            </div>
            <h3 class="card-title"><?php echo e($title); ?></h3>
        </div>
    </div>
    
    <!-- Modal Body -->
    <div class="card-body">
        <p style="color: var(--gray-600); margin: 0;">
            <?php echo e($message); ?>

            <?php echo e($slot); ?>

        </p>
    </div>
    
    <!-- Modal Footer -->
    <div class="card-footer">
        <div class="flex justify-end" style="gap: var(--space-3);">
            <button type="button" 
                    <?php if($onCancel): ?> @click="<?php echo e($onCancel); ?>" <?php else: ?> @click="<?php echo e($show); ?> = false" <?php endif; ?>
                    class="btn btn-secondary">
                <?php echo e($cancelText); ?>

            </button>
            <button type="button" 
                    <?php if($onConfirm): ?> @click="<?php echo e($onConfirm); ?>" <?php endif; ?>
                    :disabled="<?php echo e($loading); ?>"
                    class="btn <?php if($danger): ?> btn-danger <?php else: ?> btn-primary <?php endif; ?>">
                <span x-show="!<?php echo e($loading); ?>"><?php echo e($confirmText); ?></span>
                <span x-show="<?php echo e($loading); ?>" class="flex items-center" style="gap: var(--space-2);">
                    <i data-lucide="loader-2" class="w-4 h-4 smooth-spin"></i>
                    <span class="shimmer-text"><?php echo e($loadingText); ?></span>
                </span>
            </button>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56b7ba19a438470b02fb04d45bfe6840)): ?>
<?php $attributes = $__attributesOriginal56b7ba19a438470b02fb04d45bfe6840; ?>
<?php unset($__attributesOriginal56b7ba19a438470b02fb04d45bfe6840); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56b7ba19a438470b02fb04d45bfe6840)): ?>
<?php $component = $__componentOriginal56b7ba19a438470b02fb04d45bfe6840; ?>
<?php unset($__componentOriginal56b7ba19a438470b02fb04d45bfe6840); ?>
<?php endif; ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/components/modal/confirm.blade.php ENDPATH**/ ?>