<?php $__env->startSection('title', 'Agent O'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-white" x-data="chatInterface()">
    <!-- Chat Header -->
    <div class="bg-white border-b p-4" style="border-color: var(--gray-100);">
        <div class="flex items-center justify-between max-w-4xl mx-auto">
            <div class="flex items-center gap-3">
                <button onclick="window.location.href = '<?php echo e(route('conversations.index')); ?>'" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="message-square-more" class="w-5 h-5" style="color: var(--gray-600);"></i>
                </button>
                <div>
                    <h1 class="font-medium" style="color: var(--gray-900);">
                        <?php echo e($conversation->title ?? 'LAgentO'); ?>

                    </h1>
                    <p class="text-xs" style="color: var(--gray-500);">En ligne</p>
                </div>
            </div>
            <button @click="$dispatch('open-conversation-menu')" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <i data-lucide="more-vertical" class="w-5 h-5" style="color: var(--gray-600);"></i>
            </button>
        </div>
    </div>

    <!-- Messages Area -->
    <div class="max-w-4xl mx-auto p-4 space-y-4" x-ref="messagesArea">
        
        <!-- Welcome Message -->
        <?php if(empty($messages)): ?>
            <div class="text-center py-8">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center" style="background: var(--orange-lightest);">
                    <i data-lucide="brain" class="w-8 h-8" style="color: var(--orange-primary);"></i>
                </div>
                <h2 class="text-lg font-medium mb-2" style="color: var(--gray-900);">
                    Bonjour <?php echo e(auth()->user()->name ?? 'Entrepreneur'); ?> ! 👋
                </h2>
                <p class="text-sm mb-6" style="color: var(--gray-700);">
                    Je suis LAgentO, votre assistant IA entrepreneurial. Comment puis-je vous aider aujourd'hui ?
                </p>
                
                <!-- Quick Actions -->
                <div class="grid grid-cols-1 gap-3 max-w-sm mx-auto">
                    <button 
                        @click="sendQuickMessage('Comment formaliser mon entreprise en Côte d\'Ivoire ?')"
                        class="flex items-center gap-3 p-3 text-left border rounded-lg hover:shadow-sm transition-shadow"
                        style="border-color: var(--gray-200);"
                    >
                        <i data-lucide="file-text" class="w-5 h-5" style="color: var(--orange-primary);"></i>
                        <span class="text-sm" style="color: var(--gray-900);">Formaliser mon entreprise</span>
                    </button>
                    
                    <button 
                        @click="sendQuickMessage('Quels financements sont disponibles pour mon secteur ?')"
                        class="flex items-center gap-3 p-3 text-left border rounded-lg hover:shadow-sm transition-shadow"
                        style="border-color: var(--gray-200);"
                    >
                        <i data-lucide="banknote" class="w-5 h-5" style="color: var(--orange-primary);"></i>
                        <span class="text-sm" style="color: var(--gray-900);">Trouver des financements</span>
                    </button>
                    
                    <button 
                        @click="sendQuickMessage('Analyser la viabilité de mon projet')"
                        class="flex items-center gap-3 p-3 text-left border rounded-lg hover:shadow-sm transition-shadow"
                        style="border-color: var(--gray-200);"
                    >
                        <i data-lucide="trending-up" class="w-5 h-5" style="color: var(--orange-primary);"></i>
                        <span class="text-sm" style="color: var(--gray-900);">Analyser mon projet</span>
                    </button>
                    
                    <button 
                        @click="sendQuickMessage('Quelles sont mes obligations légales ?')"
                        class="flex items-center gap-3 p-3 text-left border rounded-lg hover:shadow-sm transition-shadow"
                        style="border-color: var(--gray-200);"
                    >
                        <i data-lucide="shield" class="w-5 h-5" style="color: var(--orange-primary);"></i>
                        <span class="text-sm" style="color: var(--gray-900);">Obligations légales</span>
                    </button>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Messages -->
        <?php $__currentLoopData = $messages ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <!-- User Message -->
            <?php if($message->role === 'user'): ?>
                <div class="flex justify-end">
                    <div class="max-w-xs lg:max-w-md px-4 py-3 rounded-lg" style="background: var(--orange-primary); color: white;">
                        <p class="text-sm"><?php echo e($message->content); ?></p>
                        <div class="text-xs mt-1 opacity-80">
                            <?php echo e($message->created_at->format('H:i')); ?>

                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Assistant Message -->
            <?php if($message->role === 'assistant'): ?>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background: var(--orange-lightest);">
                        <i data-lucide="brain" class="w-4 h-4" style="color: var(--orange-primary);"></i>
                    </div>
                    
                    <div class="flex-1">
                        <?php echo app(\App\Services\MarkdownProcessor::class)->process($message->content); ?>

                        
                        <!-- Message Actions -->
                        <div class="flex items-center gap-2 mt-3">
                            <button 
                                @click="copyMessage('<?php echo e(addslashes($message->content)); ?>')"
                                class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                                title="Copier"
                            >
                                <i data-lucide="copy" class="w-4 h-4" style="color: var(--gray-500);"></i>
                            </button>
                            
                            <button 
                                @click="retryMessage('<?php echo e(addslashes($message->content)); ?>')"
                                class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                                title="Régénérer"
                            >
                                <i data-lucide="refresh-cw" class="w-4 h-4" style="color: var(--gray-500);"></i>
                            </button>
                            
                            <span class="text-xs ml-auto" style="color: var(--gray-500);">
                                <?php echo e($message->created_at->format('H:i')); ?>

                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        
        <!-- Typing Indicator -->
        <div x-show="isTyping" class="flex items-start gap-3" style="display: none;">
            <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: var(--orange-lightest);">
                <i data-lucide="brain" class="w-4 h-4" style="color: var(--orange-primary);"></i>
            </div>
            <div class="flex items-center gap-1 p-3 rounded-lg" style="background: var(--gray-100);">
                <div class="w-2 h-2 rounded-full animate-pulse" style="background: var(--gray-500);"></div>
                <div class="w-2 h-2 rounded-full animate-pulse" style="background: var(--gray-500); animation-delay: 0.2s;"></div>
                <div class="w-2 h-2 rounded-full animate-pulse" style="background: var(--gray-500); animation-delay: 0.4s;"></div>
            </div>
        </div>
    </div>

</div>

<?php $__env->startPush('scripts'); ?>
<script>
function chatInterface() {
    return {
        copyMessage(content) {
            navigator.clipboard.writeText(content).then(() => {
                // Show temporary feedback
                const button = event.target.closest('button');
                const icon = button.querySelector('i');
                icon.setAttribute('data-lucide', 'check');
                setTimeout(() => {
                    icon.setAttribute('data-lucide', 'copy');
                }, 1000);
            });
        },
        
        sendQuickMessage(text) {
            // Use the fixed chat form
            const fixedChatComponent = document.querySelector('[x-data*="fixedChat"]');
            if (fixedChatComponent && fixedChatComponent._x_dataStack) {
                fixedChatComponent._x_dataStack[0].message = text;
                fixedChatComponent._x_dataStack[0].sendMessage();
            }
        }
    }
}

// Custom card interactions
function contactInstitution() {
    // TODO: Implement contact functionality
    console.log('Contact institution clicked');
}

function viewMore() {
    // TODO: Implement view more functionality
    console.log('View more clicked');
}

function applyOpportunity() {
    // TODO: Implement apply functionality
    console.log('Apply opportunity clicked');
}

function saveOpportunity() {
    // TODO: Implement save functionality
    console.log('Save opportunity clicked');
}

function downloadText() {
    // TODO: Implement download functionality
    console.log('Download text clicked');
}

function viewFullText() {
    // TODO: Implement view full text functionality
    console.log('View full text clicked');
}

function connectPartner() {
    // TODO: Implement connect partner functionality
    console.log('Connect partner clicked');
}

function viewProfile() {
    // TODO: Implement view profile functionality
    console.log('View profile clicked');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/laminebarro/agent-O/resources/views/chat.blade.php ENDPATH**/ ?>