<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> - LAgentO</title>
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', 'Assistant IA entrepreneurial pour la Côte d\'Ivoire'); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon-light.png" id="favicon">
    <link rel="apple-touch-icon" href="/favicon-light.png">
    
    <!-- Theme Script (doit être avant les styles) -->
    <script>
        // Appliquer le thème avant le rendu pour éviter les flashes
        (function() {
            const theme = localStorage.getItem('theme') || 
                         (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
            
            // Update favicon
            const favicon = document.getElementById('favicon');
            if (favicon) {
                favicon.href = theme === 'dark' ? '/favicon-dark.png' : '/favicon-light.png';
            }
        })();
    </script>
    
    <!-- Styles -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body x-data="{}" style="background: var(--gray-50);">
    <div class="page">
        <!-- Header -->
        <?php echo $__env->make('components.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <!-- Sidebar mobile supprimé -->
        
        <!-- Contenu principal -->
        <main class="main" style="padding-bottom: var(--chat-height);">
            <?php echo $__env->yieldContent('content'); ?>
        </main>

        <!-- Chat fixe -->
        <?php if(auth()->guard()->check()): ?>
        <div class="fixed bottom-0 left-0 right-0 z-sticky" 
             style="background: var(--white); border-top: 1px solid var(--gray-200);" x-data="fixedChat()">
            <div class="container max-w-4xl mx-auto p-4">
                <!-- Suggestions tooltip (cachées par défaut) -->
                <div id="suggestions-tooltip" class="mb-3 rounded-xl shadow-sm p-3 hidden" style="background: var(--white); border: 1px solid var(--gray-200);">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-medium" style="color: var(--gray-600);">Suggestions</span>
                        <button type="button" id="refresh-suggestions" class="p-1 rounded transition-colors" style="color: var(--gray-600);" onmouseover="this.style.background='var(--gray-100)'" onmouseout="this.style.background='transparent'" title="Rafraîchir">
                            <i data-lucide="refresh-cw" class="w-3 h-3"></i>
                        </button>
                    </div>
                    <div class="space-y-2">
                        <button type="button" class="suggestion-item w-full text-left px-3 py-2 text-sm rounded-md transition-colors" style="color: var(--gray-900);" onmouseover="this.style.background='var(--gray-100)'" onmouseout="this.style.background='transparent'" onclick="document.querySelector('textarea').value = this.dataset.text; document.querySelector('textarea').focus(); document.getElementById('suggestions-tooltip').classList.add('hidden');" data-text="Aide-moi à créer un business plan détaillé pour ma startup">Aide-moi à créer un business plan détaillé pour ma startup</button>
                        <button type="button" class="suggestion-item w-full text-left px-3 py-2 text-sm rounded-md transition-colors" style="color: var(--gray-900);" onmouseover="this.style.background='var(--gray-100)'" onmouseout="this.style.background='transparent'" onclick="document.querySelector('textarea').value = this.dataset.text; document.querySelector('textarea').focus(); document.getElementById('suggestions-tooltip').classList.add('hidden');" data-text="Quelles sont les opportunités de financement disponibles en Côte d'Ivoire ?">Quelles sont les opportunités de financement disponibles en Côte d'Ivoire ?</button>
                        <button type="button" class="suggestion-item w-full text-left px-3 py-2 text-sm rounded-md transition-colors" style="color: var(--gray-900);" onmouseover="this.style.background='var(--gray-100)'" onmouseout="this.style.background='transparent'" onclick="document.querySelector('textarea').value = this.dataset.text; document.querySelector('textarea').focus(); document.getElementById('suggestions-tooltip').classList.add('hidden');" data-text="Comment valider mon idée d'entreprise avant de me lancer ?">Comment valider mon idée d'entreprise avant de me lancer ?</button>
                        <button type="button" class="suggestion-item w-full text-left px-3 py-2 text-sm rounded-md transition-colors" style="color: var(--gray-900);" onmouseover="this.style.background='var(--gray-100)'" onmouseout="this.style.background='transparent'" onclick="document.querySelector('textarea').value = this.dataset.text; document.querySelector('textarea').focus(); document.getElementById('suggestions-tooltip').classList.add('hidden');" data-text="Crée-moi une stratégie marketing pour lancer ma startup en Afrique">Crée-moi une stratégie marketing pour lancer ma startup en Afrique</button>
                        <button type="button" class="suggestion-item w-full text-left px-3 py-2 text-sm rounded-md transition-colors" style="color: var(--gray-900);" onmouseover="this.style.background='var(--gray-100)'" onmouseout="this.style.background='transparent'" onclick="document.querySelector('textarea').value = this.dataset.text; document.querySelector('textarea').focus(); document.getElementById('suggestions-tooltip').classList.add('hidden');" data-text="Quelles sont les étapes légales pour créer une entreprise en Côte d'Ivoire ?">Quelles sont les étapes légales pour créer une entreprise en Côte d'Ivoire ?</button>
                    </div>
                </div>

                <!-- Modern chat input container -->
                <form @submit.prevent="sendMessage" class="relative">
                    <div class="relative rounded-2xl shadow-sm transition-all duration-200" style="background: var(--white); border: 1px solid var(--gray-200);" 
                         onfocusin="this.style.borderColor='var(--orange)'; this.style.boxShadow='0 0 0 3px rgba(255, 107, 53, 0.1)'" 
                         onfocusout="this.style.borderColor='var(--gray-200)'; this.style.boxShadow='none'">
                        
                        <!-- Aperçu fichier -->
                        <div x-show="attachedFile" class="absolute bottom-full mb-2 p-2 rounded-lg flex items-center gap-2" style="background: var(--gray-100); border: 1px solid var(--gray-200);">
                            <i data-lucide="paperclip" class="w-4 h-4" style="color: var(--gray-500);"></i>
                            <span x-text="attachedFile?.name" class="text-sm flex-1" style="color: var(--gray-700);"></span>
                            <button type="button" @click="removeAttachment()" class="p-1 rounded transition-colors" style="color: var(--gray-500);" onmouseover="this.style.background='var(--gray-200)'" onmouseout="this.style.background='transparent'">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <textarea 
                            x-model="message"
                            @keydown.enter.prevent="if (!$event.shiftKey) sendMessage()"
                            placeholder="Posez votre question à LAgentO..."
                            rows="1"
                            class="w-full min-h-[52px] max-h-[100px] bg-transparent rounded-2xl p-4 pt-6 pb-12 text-sm resize-none overflow-y-auto"
                            style="color: var(--gray-900); outline: none; border: none;"
                            oninput="this.style.height = 'auto'; this.style.height = Math.min(this.scrollHeight, 100) + 'px'"
                        ></textarea>
                        
                        <!-- Bottom action bar -->
                        <div class="flex items-center justify-between px-4 py-2">
                            <!-- Left icons -->
                            <div class="flex items-center gap-3">
                                <input 
                                    type="file" 
                                    x-ref="fileInput" 
                                    @change="handleFileUpload"
                                    accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png"
                                    class="hidden"
                                />
                                <button type="button" @click="$refs.fileInput.click()" 
                                        class="p-1.5 rounded-lg transition-all" 
                                        style="color: var(--gray-600);" 
                                        onmouseover="this.style.color='var(--gray-900)'; this.style.background='var(--gray-100)'" 
                                        onmouseout="this.style.color='var(--gray-600)'; this.style.background='transparent'"
                                        title="Joindre un fichier">
                                    <i data-lucide="paperclip" class="w-4 h-4"></i>
                                </button>
                                <button type="button" 
                                        class="p-1.5 rounded-lg transition-all" 
                                        style="color: var(--gray-600);" 
                                        onmouseover="this.style.color='var(--gray-900)'; this.style.background='var(--gray-100)'" 
                                        onmouseout="this.style.color='var(--gray-600)'; this.style.background='transparent'"
                                        onclick="document.getElementById('suggestions-tooltip').classList.toggle('hidden')"
                                        title="Suggestions">
                                    <i data-lucide="lightbulb" class="w-4 h-4"></i>
                                </button>
                                <button type="button" 
                                        class="p-1.5 rounded-lg transition-all" 
                                        style="color: var(--gray-600);" 
                                        onmouseover="this.style.color='var(--gray-900)'; this.style.background='var(--gray-100)'" 
                                        onmouseout="this.style.color='var(--gray-600)'; this.style.background='transparent'"
                                        title="Enregistrement vocal">
                                    <i data-lucide="mic" class="w-4 h-4"></i>
                                </button>
                            </div>
                            
                            <!-- Send button -->
                            <button 
                                type="submit"
                                :disabled="!message.trim() && !attachedFile || isLoading"
                                class="p-2 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-offset-2"
                                :class="(!message.trim() && !attachedFile) || isLoading ? 'cursor-not-allowed' : ''"
                                :style="(!message.trim() && !attachedFile) || isLoading ? 'background: var(--gray-300); color: var(--gray-500);' : 'background: var(--orange-primary); color: white;'"
                                onmouseover="if (!this.disabled) this.style.background='var(--orange-dark)'"
                                onmouseout="if (!this.disabled) this.style.background='var(--orange-primary)'"
                            >
                                <i data-lucide="send-horizontal" class="w-4 h-4" x-show="!isLoading"></i>
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="isLoading"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php echo $__env->yieldPushContent('scripts'); ?>

    <?php if(auth()->guard()->check()): ?>
    <script>
    function fixedChat() {
        return {
            message: '',
            isLoading: false,
            attachedFile: null,
            
            sendMessage() {
                if ((!this.message.trim() && !this.attachedFile) || this.isLoading) return;
                
                const userMessage = this.message;
                const file = this.attachedFile;
                
                this.message = '';
                this.attachedFile = null;
                this.isLoading = true;
                
                const formData = new FormData();
                formData.append('message', userMessage);
                
                const conversationId = document.querySelector('[data-conversation-id]')?.getAttribute('data-conversation-id');
                if (conversationId) {
                    formData.append('conversation_id', conversationId);
                }
                if (file) {
                    formData.append('file', file);
                }
                
                fetch('<?php echo e(route("chat.send")); ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(() => {
                    window.location.href = '<?php echo e(route("chat")); ?>';
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de l\'envoi du message');
                })
                .finally(() => {
                    this.isLoading = false;
                });
            },
            
            handleFileUpload(event) {
                const file = event.target.files[0];
                if (file && file.size <= 5 * 1024 * 1024) {
                    this.attachedFile = file;
                } else if (file) {
                    alert('Le fichier ne doit pas dépasser 5MB');
                    event.target.value = '';
                }
            },
            
            removeAttachment() {
                this.attachedFile = null;
                this.$refs.fileInput.value = '';
            }
        }
    }
    
    // Fonctions pour les conversations  
    function closeConversation(button) {
        const tab = button.closest('.conversation-tab');
        tab.remove();
    }
    
    function createNewConversation() {
        window.location.href = '<?php echo e(route("chat")); ?>';
    }
    </script>
    
    <style>
    /* Hide scrollbar but keep functionality */
    .scrollbar-hidden {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .scrollbar-hidden::-webkit-scrollbar {
        display: none;
    }
    </style>
    <?php endif; ?>
</body>
</html>
<?php /**PATH /Users/laminebarro/agent-O/resources/views/layouts/app.blade.php ENDPATH**/ ?>