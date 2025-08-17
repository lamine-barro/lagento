<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'otp',
    'length' => 6,
    'autofocus' => true
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
    'name' => 'otp',
    'length' => 6,
    'autofocus' => true
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div 
    x-data="otpInput()"
    x-init="init()"
    class="otp-inputs"
>
    <?php for($i = 0; $i < $length; $i++): ?>
        <input 
            type="text"
            maxlength="1"
            class="otp-digit"
            x-ref="input<?php echo e($i); ?>"
            @input="handleInput($event, <?php echo e($i); ?>)"
            @keydown="handleKeyDown($event, <?php echo e($i); ?>)"
            @paste="handlePaste($event)"
            <?php if($autofocus && $i === 0): ?> autofocus <?php endif; ?>
        />
    <?php endfor; ?>
    
    <input type="hidden" name="<?php echo e($name); ?>" x-model="otpValue" />
</div>

<script>
function otpInput() {
    return {
        inputs: [],
        otpValue: '',
        
        init() {
            this.inputs = Array.from({ length: <?php echo e($length); ?> }, (_, i) => this.$refs[`input${i}`]);
        },
        
        handleInput(event, index) {
            const value = event.target.value;
            
            if (value && index < <?php echo e($length - 1); ?>) {
                this.inputs[index + 1].focus();
            }
            
            this.updateOtpValue();
        },
        
        handleKeyDown(event, index) {
            if (event.key === 'Backspace' && !event.target.value && index > 0) {
                this.inputs[index - 1].focus();
            }
        },
        
        handlePaste(event) {
            event.preventDefault();
            const pastedData = event.clipboardData.getData('text').slice(0, <?php echo e($length); ?>);
            
            pastedData.split('').forEach((char, i) => {
                if (this.inputs[i]) {
                    this.inputs[i].value = char;
                }
            });
            
            const lastFilledIndex = Math.min(pastedData.length - 1, <?php echo e($length - 1); ?>);
            this.inputs[lastFilledIndex].focus();
            
            this.updateOtpValue();
        },
        
        updateOtpValue() {
            this.otpValue = this.inputs.map(input => input.value).join('');
        }
    }
}
</script><?php /**PATH /Users/laminebarro/agent-O/resources/views/components/otp-input.blade.php ENDPATH**/ ?>