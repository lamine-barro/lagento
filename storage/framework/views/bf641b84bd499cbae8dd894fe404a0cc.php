<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'info',
    'dismissible' => false,
    'icon' => null
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
    'type' => 'info',
    'dismissible' => false,
    'icon' => null
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $classes = 'alert';
    
    $classes .= match($type) {
        'success' => ' alert-success',
        'warning' => ' alert-warning',
        'danger' => ' alert-danger',
        'error' => ' alert-danger',
        default => ' alert-info'
    };
    
    $defaultIcon = match($type) {
        'success' => 'check-circle',
        'warning' => 'alert-triangle',
        'danger', 'error' => 'x-circle',
        default => 'info'
    };
    
    $icon = $icon ?? $defaultIcon;
?>

<div 
    <?php echo e($attributes->merge(['class' => $classes])); ?>

    <?php if($dismissible): ?>
        x-data="{ show: true }"
        x-show="show"
        x-transition
    <?php endif; ?>
>
    <i data-lucide="<?php echo e($icon); ?>" class="w-5 h-5"></i>
    
    <div class="flex-1">
        <?php echo e($slot); ?>

    </div>
    
    <?php if($dismissible): ?>
        <button 
            @click="show = false"
            class="ml-auto p-1 hover:bg-white/20 rounded transition"
        >
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    <?php endif; ?>
</div><?php /**PATH /Users/laminebarro/agent-O/resources/views/components/alert.blade.php ENDPATH**/ ?>