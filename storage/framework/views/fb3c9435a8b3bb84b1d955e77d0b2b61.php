<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'subtitle' => null,
    'interactive' => false,
    'padding' => true,
    'footer' => null,
    'headerActions' => null
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
    'title' => null,
    'subtitle' => null,
    'interactive' => false,
    'padding' => true,
    'footer' => null,
    'headerActions' => null
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $classes = 'card';
    
    if ($interactive) {
        $classes .= ' card-interactive';
    }
    
    if (!$padding) {
        $classes = str_replace('card', 'card p-0', $classes);
    }
?>

<div <?php echo e($attributes->merge(['class' => $classes])); ?>>
    <?php if($title || $headerActions): ?>
        <div class="card-header flex justify-between items-center">
            <div>
                <?php if($title): ?>
                    <h3 class="card-title"><?php echo e($title); ?></h3>
                <?php endif; ?>
                <?php if($subtitle): ?>
                    <p class="text-sm text-gray-500 mt-1"><?php echo e($subtitle); ?></p>
                <?php endif; ?>
            </div>
            <?php if($headerActions): ?>
                <div class="flex gap-2">
                    <?php echo e($headerActions); ?>

                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="card-body">
        <?php echo e($slot); ?>

    </div>
    
    <?php if($footer): ?>
        <div class="card-footer">
            <?php echo e($footer); ?>

        </div>
    <?php endif; ?>
</div><?php /**PATH /Users/laminebarro/agent-O/resources/views/components/card.blade.php ENDPATH**/ ?>