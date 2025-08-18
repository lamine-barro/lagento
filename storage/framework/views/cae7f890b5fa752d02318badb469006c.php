<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'show' => 'false',
    'title' => '',
    'maxWidth' => 'md',
    'closable' => true,
    'showHeader' => true,
    'showFooter' => false,
    'onClose' => '',
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
    'title' => '',
    'maxWidth' => 'md',
    'closable' => true,
    'showHeader' => true,
    'showFooter' => false,
    'onClose' => '',
    'zIndex' => '99999'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (isset($component)) { $__componentOriginal56b7ba19a438470b02fb04d45bfe6840 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56b7ba19a438470b02fb04d45bfe6840 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.base','data' => ['show' => $show,'maxWidth' => $maxWidth,'closable' => $closable,'zIndex' => $zIndex]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal.base'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['show' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($show),'max-width' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($maxWidth),'closable' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($closable),'z-index' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($zIndex)]); ?>
    <?php if($showHeader && $title): ?>
        <!-- Modal Header -->
        <div class="card-header">
            <div class="flex items-center justify-between">
                <h3 class="card-title"><?php echo e($title); ?></h3>
                <?php if($closable): ?>
                    <button type="button" 
                            <?php if($onClose): ?> @click="<?php echo e($onClose); ?>" <?php else: ?> @click="<?php echo e($show); ?> = false" <?php endif; ?>
                            class="p-2 rounded-lg transition-colors"
                            style="color: var(--gray-500);"
                            onmouseover="this.style.background='var(--gray-100)'"
                            onmouseout="this.style.background='transparent'">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Modal Body -->
    <div class="card-body">
        <?php echo e($slot); ?>

    </div>
    
    <?php if($showFooter): ?>
        <!-- Modal Footer -->
        <div class="card-footer">
            <?php echo e($footer ?? ''); ?>

        </div>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56b7ba19a438470b02fb04d45bfe6840)): ?>
<?php $attributes = $__attributesOriginal56b7ba19a438470b02fb04d45bfe6840; ?>
<?php unset($__attributesOriginal56b7ba19a438470b02fb04d45bfe6840); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56b7ba19a438470b02fb04d45bfe6840)): ?>
<?php $component = $__componentOriginal56b7ba19a438470b02fb04d45bfe6840; ?>
<?php unset($__componentOriginal56b7ba19a438470b02fb04d45bfe6840); ?>
<?php endif; ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/components/modal/generic.blade.php ENDPATH**/ ?>