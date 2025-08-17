<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'role' => 'user',
    'content' => '',
    'timestamp' => null,
    'status' => null
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
    'role' => 'user',
    'content' => '',
    'timestamp' => null,
    'status' => null
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="message <?php echo e($role); ?>">
    <?php if($role === 'assistant'): ?>
        <div class="message-content">
            <div class="message-text">
                <?php echo Str::markdown($content); ?>

            </div>
            
            <div class="message-actions">
                <button 
                    class="action-btn"
                    @click="navigator.clipboard.writeText('<?php echo e(addslashes($content)); ?>')"
                    title="Copier"
                >
                    <i data-lucide="copy" class="w-4 h-4"></i>
                </button>
                
                <button 
                    class="action-btn"
                    title="Régénérer"
                >
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                </button>
            </div>
            
            <?php if($timestamp): ?>
                <div class="text-xs text-gray-500 mt-2">
                    <?php echo e($timestamp); ?>

                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="user-message">
            <?php echo e($content); ?>

            
            <?php if($status === 'sending'): ?>
                <div class="inline-block ml-2">
                    <div class="loading-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if($timestamp): ?>
            <div class="text-xs text-gray-500 mt-1 text-right">
                <?php echo e($timestamp); ?>

            </div>
        <?php endif; ?>
    <?php endif; ?>
</div><?php /**PATH /Users/laminebarro/agent-O/resources/views/components/chat-message.blade.php ENDPATH**/ ?>