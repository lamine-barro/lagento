<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'modal',
    'title' => '',
    'maxWidth' => 'md',
    'footer' => null
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
    'name' => 'modal',
    'title' => '',
    'maxWidth' => 'md',
    'footer' => null
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
        'full' => 'max-w-full',
        default => 'max-w-md'
    };
?>

<div 
    x-data="{ open: false }"
    x-on:open-<?php echo e($name); ?>.window="open = true"
    x-on:close-<?php echo e($name); ?>.window="open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    class="fixed inset-0 z-50"
    style="display: none;"
>
    <!-- Backdrop -->
    <div 
        x-show="open"
        x-transition:enter="fade-in"
        x-transition:leave="fade-out"
        class="modal-backdrop"
        @click="open = false"
    >
        <!-- Modal -->
        <div 
            x-show="open"
            x-transition:enter="scale-in"
            x-transition:leave="scale-out"
            @click.stop
            class="modal <?php echo e($maxWidthClass); ?>"
        >
            <?php if($title): ?>
                <div class="modal-header">
                    <h2 class="modal-title"><?php echo e($title); ?></h2>
                    <button 
                        @click="open = false"
                        class="p-2 hover:bg-gray-100 rounded-lg transition"
                    >
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            <?php endif; ?>
            
            <div class="modal-body">
                <?php echo e($slot); ?>

            </div>
            
            <?php if($footer): ?>
                <div class="modal-footer">
                    <?php echo e($footer); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div><?php /**PATH /Users/laminebarro/agent-O/resources/views/components/modal.blade.php ENDPATH**/ ?>