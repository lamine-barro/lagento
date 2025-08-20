<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'text',
    'lines' => 3,
    'height' => null,
    'width' => null,
    'rounded' => false
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
    'type' => 'text',
    'lines' => 3,
    'height' => null,
    'width' => null,
    'rounded' => false
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $classes = 'skeleton';
    
    $classes .= match($type) {
        'text' => ' skeleton-text',
        'title' => ' skeleton-title',
        'avatar' => ' skeleton-avatar',
        'button' => ' skeleton-button',
        'card' => ' skeleton-card',
        default => ''
    };
    
    if ($rounded) {
        $classes .= ' rounded-full';
    }
    
    $style = '';
    if ($height) {
        $style .= "height: {$height};";
    }
    if ($width) {
        $style .= "width: {$width};";
    }
?>

<?php if($type === 'text' && $lines > 1): ?>
    <?php for($i = 0; $i < $lines; $i++): ?>
        <div 
            class="<?php echo e($classes); ?>"
            <?php if($i === $lines - 1 && $lines > 1): ?> style="width: 80%;" <?php endif; ?>
        ></div>
    <?php endfor; ?>
<?php else: ?>
    <div 
        <?php echo e($attributes->merge(['class' => $classes])); ?>

        <?php if($style): ?> style="<?php echo e($style); ?>" <?php endif; ?>
    ></div>
<?php endif; ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/components/skeleton.blade.php ENDPATH**/ ?>