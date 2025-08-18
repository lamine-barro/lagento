<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'show' => false,
    'maxWidth' => 'md',
    'closable' => true,
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
    'show' => false,
    'maxWidth' => 'md',
    'closable' => true,
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
    $maxWidthClass = match($maxWidth) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        'full' => 'max-w-full',
        default => 'max-w-md'
    };
?>

<template x-teleport="body">
    <div x-show="<?php echo e($show); ?>" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 overflow-y-auto" 
         style="z-index: <?php echo e($zIndex); ?> !important; display: none;">
        
        <!-- Blurred Backdrop -->
        <div class="fixed inset-0" 
             style="background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px);" 
             <?php if($closable): ?> @click="<?php echo e($show); ?> = false" <?php endif; ?>></div>
        
        <!-- Modal Container -->
        <div class="flex items-center justify-center min-h-screen" style="padding: var(--space-4); position: relative; z-index: <?php echo e($zIndex + 1); ?>;">
            <div x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="card <?php echo e($maxWidthClass); ?> w-full"
                 style="position: relative; z-index: <?php echo e($zIndex + 2); ?>;"
                 @click.stop>
                
                <?php echo e($slot); ?>

                
            </div>
        </div>
    </div>
</template><?php /**PATH /Users/laminebarro/agent-O/resources/views/components/modal/base.blade.php ENDPATH**/ ?>