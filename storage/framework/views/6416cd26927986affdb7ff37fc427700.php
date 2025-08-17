<?php $__env->startSection('title', 'Vérification - Agent O'); ?>

<?php $__env->startSection('content'); ?>
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
                Code envoyé à <span class="font-medium"><?php echo e(session('email') ?? 'votre email'); ?></span>
            </p>
        </div>

        <!-- OTP Form -->
        <form method="POST" action="<?php echo e(route('auth.verify-otp')); ?>" x-data="otpForm()">
            <?php echo csrf_field(); ?>
            
            <!-- OTP Inputs -->
            <div class="flex justify-center gap-3 mb-6">
                <?php for($i = 0; $i < 6; $i++): ?>
                    <input 
                        type="text"
                        maxlength="1"
                        class="w-12 h-14 text-center text-xl font-medium border"
                        style="border-color: var(--black); border-radius: var(--radius-md);"
                        x-ref="input<?php echo e($i); ?>"
                        @input="handleInput($event, <?php echo e($i); ?>)"
                        @keydown="handleKeyDown($event, <?php echo e($i); ?>)"
                        @paste="handlePaste($event)"
                        <?php if($i === 0): ?> autofocus <?php endif; ?>
                    />
                <?php endfor; ?>
            </div>

            <!-- Hidden Input -->
            <input type="hidden" name="otp" x-model="otpValue" />

            <!-- Error Message -->
            <?php $__errorArgs = ['otp'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="alert alert-danger mb-4">
                    <i data-lucide="x-circle" class="w-5 h-5"></i>
                    <?php echo e($message); ?>

                </div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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
            
            <form method="POST" action="<?php echo e(route('auth.resend-otp')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-ghost">
                    Renvoyer le code
                </button>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
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
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/auth/verify-otp.blade.php ENDPATH**/ ?>