<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left',
    'loading' => false,
    'fullWidth' => false
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
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left',
    'loading' => false,
    'fullWidth' => false
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $classes = 'btn';
    
    // Variant classes
    $classes .= match($variant) {
        'primary' => ' btn-primary',
        'secondary' => ' btn-secondary',
        'ghost' => ' btn-ghost',
        default => ' btn-primary'
    };
    
    // Size classes
    $classes .= match($size) {
        'sm' => ' btn-sm',
        'lg' => ' btn-lg',
        default => ''
    };
    
    // Full width
    if ($fullWidth) {
        $classes .= ' w-full';
    }
    
    // Disabled state
    if ($disabled || $loading) {
        $classes .= ' state-disabled';
    }
?>

<button 
    type="<?php echo e($type); ?>"
    <?php echo e($attributes->merge(['class' => $classes])); ?>

    <?php if($disabled || $loading): echo 'disabled'; endif; ?>
>
    <?php if($loading): ?>
        <div class="spinner spinner-sm"></div>
    <?php else: ?>
        <?php if($icon && $iconPosition === 'left'): ?>
            <i data-lucide="<?php echo e($icon); ?>"></i>
        <?php endif; ?>
        
        <?php echo e($slot); ?>

        
        <?php if($icon && $iconPosition === 'right'): ?>
            <i data-lucide="<?php echo e($icon); ?>"></i>
        <?php endif; ?>
    <?php endif; ?>
</button><?php /**PATH /Users/laminebarro/agent-O/resources/views/components/button.blade.php ENDPATH**/ ?>