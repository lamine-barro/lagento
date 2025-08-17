@props([
    'name' => 'otp',
    'length' => 6,
    'autofocus' => true
])

<div 
    x-data="otpInput()"
    x-init="init()"
    class="otp-inputs"
>
    @for($i = 0; $i < $length; $i++)
        <input 
            type="text"
            maxlength="1"
            class="otp-digit"
            x-ref="input{{ $i }}"
            @input="handleInput($event, {{ $i }})"
            @keydown="handleKeyDown($event, {{ $i }})"
            @paste="handlePaste($event)"
            @if($autofocus && $i === 0) autofocus @endif
        />
    @endfor
    
    <input type="hidden" name="{{ $name }}" x-model="otpValue" />
</div>

<script>
function otpInput() {
    return {
        inputs: [],
        otpValue: '',
        
        init() {
            this.inputs = Array.from({ length: {{ $length }} }, (_, i) => this.$refs[`input${i}`]);
        },
        
        handleInput(event, index) {
            const value = event.target.value;
            
            if (value && index < {{ $length - 1 }}) {
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
            const pastedData = event.clipboardData.getData('text').slice(0, {{ $length }});
            
            pastedData.split('').forEach((char, i) => {
                if (this.inputs[i]) {
                    this.inputs[i].value = char;
                }
            });
            
            const lastFilledIndex = Math.min(pastedData.length - 1, {{ $length - 1 }});
            this.inputs[lastFilledIndex].focus();
            
            this.updateOtpValue();
        },
        
        updateOtpValue() {
            this.otpValue = this.inputs.map(input => input.value).join('');
        }
    }
}
</script>