<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'text',
    'name' => '',
    'id' => null,
    'label' => null,
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'hint' => null,
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
    'type' => 'text',
    'name' => '',
    'id' => null,
    'label' => null,
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'hint' => null,
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
    $inputId = $id ?? $name;
    $hasError = $error || $errors->has($name);
    $inputClasses = 'input-field';
    
    if ($hasError) {
        $inputClasses .= ' error';
    }
?>

<div class="form-group">
    <?php if($label): ?>
        <label for="<?php echo e($inputId); ?>" class="form-label">
            <?php echo e($label); ?>

            <?php if($required): ?>
                <span class="text-danger">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>
    
    <div class="relative">
        <?php if($icon): ?>
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                <i data-lucide="<?php echo e($icon); ?>" class="w-5 h-5"></i>
            </div>
        <?php endif; ?>
        
        <input
            type="<?php echo e($type); ?>"
            name="<?php echo e($name); ?>"
            id="<?php echo e($inputId); ?>"
            placeholder="<?php echo e($placeholder); ?>"
            value="<?php echo e(old($name, $value)); ?>"
            <?php echo e($attributes->merge(['class' => $inputClasses . ($icon ? ' pl-10' : '')])); ?>

            <?php if($required): echo 'required'; endif; ?>
            <?php if($disabled): echo 'disabled'; endif; ?>
        />
    </div>
    
    <?php if($hasError): ?>
        <div class="text-danger text-sm mt-1">
            <?php echo e($error ?? $errors->first($name)); ?>

        </div>
    <?php endif; ?>
    
    <?php if($hint): ?>
        <div class="text-gray-500 text-sm mt-1">
            <?php echo e($hint); ?>

        </div>
    <?php endif; ?>
</div><?php /**PATH /Users/laminebarro/agent-O/resources/views/components/input.blade.php ENDPATH**/ ?>