@extends('layouts.guest')

@section('title', 'Vérification - Agent O')

@section('content')
<div class="min-h-screen bg-white flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Back Button -->
        <button 
            onclick="history.back()" 
            class="btn btn-ghost mb-6 p-2"
        >
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </button>

        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-medium mb-2" style="color: var(--gray-900);">
                Vérification
            </h1>
            <p style="color: var(--gray-700);">
                Code envoyé à <span class="font-medium">{{ session('email') ?? 'votre email' }}</span>
            </p>
        </div>

        <!-- OTP Form -->
        <form method="POST" action="{{ route('auth.verify-otp') }}" x-data="otpForm()">
            @csrf
            
            <!-- OTP Inputs -->
            <div class="flex justify-center gap-3 mb-6">
                @for($i = 0; $i < 6; $i++)
                    <input 
                        type="text"
                        maxlength="1"
                        class="w-12 h-14 text-center text-xl font-medium border"
                        style="border-color: var(--black); border-radius: var(--radius-md);"
                        x-ref="input{{ $i }}"
                        @input="handleInput($event, {{ $i }})"
                        @keydown="handleKeyDown($event, {{ $i }})"
                        @paste="handlePaste($event)"
                        @if($i === 0) autofocus @endif
                    />
                @endfor
            </div>

            <!-- Hidden Input -->
            <input type="hidden" name="otp" x-model="otpValue" />

            <!-- Error Message -->
            @error('otp')
                <div class="alert alert-danger mb-4">
                    <i data-lucide="x-circle" class="w-5 h-5"></i>
                    {{ $message }}
                </div>
            @enderror

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="btn btn-primary w-full mb-6"
                :disabled="otpValue.length < 6"
            >
                Vérifier le code
            </button>
        </form>

        <!-- Resend -->
        <div class="text-center">
            <p class="text-sm mb-3" style="color: var(--gray-500);">
                Vous n'avez pas reçu le code ?
            </p>
            
            <form method="POST" action="{{ route('auth.resend-otp') }}">
                @csrf
                <button type="submit" class="btn btn-ghost">
                    Renvoyer le code
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function otpForm() {
    return {
        inputs: [],
        otpValue: '',
        
        init() {
            this.inputs = Array.from({ length: 6 }, (_, i) => this.$refs[`input${i}`]);
        },
        
        handleInput(event, index) {
            const value = event.target.value;
            
            // Only allow digits
            if (!/^\d*$/.test(value)) {
                event.target.value = '';
                return;
            }
            
            if (value && index < 5) {
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
            const pastedData = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
            
            pastedData.split('').forEach((char, i) => {
                if (this.inputs[i]) {
                    this.inputs[i].value = char;
                }
            });
            
            const lastFilledIndex = Math.min(pastedData.length - 1, 5);
            this.inputs[lastFilledIndex].focus();
            
            this.updateOtpValue();
        },
        
        updateOtpValue() {
            this.otpValue = this.inputs.map(input => input.value).join('');
        }
    }
}
</script>
@endpush
@endsection