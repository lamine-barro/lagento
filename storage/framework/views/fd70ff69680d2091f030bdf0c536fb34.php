<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    
    <!-- SEO Meta Tags -->
    <title><?php echo $__env->yieldContent('page_title'); ?></title>
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', 'Assistant IA spécialisé pour entrepreneurs ivoiriens. Conseils business, opportunités de financement, diagnostic d\'entreprise et accompagnement personnalisé 24/7.'); ?>">
    <meta name="keywords" content="<?php echo $__env->yieldContent('meta_keywords', 'assistant IA côte ivoire, entrepreneur ivoirien, startup abidjan, financement PME, diagnostic entreprise, conseil business, innovation afrique'); ?>">
    <meta name="author" content="LAgentO - Lamine Barro">
    <meta name="robots" content="<?php echo $__env->yieldContent('meta_robots', 'index, follow'); ?>">
    <link rel="canonical" href="<?php echo $__env->yieldContent('canonical_url', url()->current()); ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:site_name" content="LAgentO">
    <meta property="og:title" content="<?php echo $__env->yieldContent('og_title', 'LAgentO - Assistant IA Entrepreneurial Côte d\'Ivoire'); ?>">
    <meta property="og:description" content="<?php echo $__env->yieldContent('og_description', 'Assistant IA spécialisé pour entrepreneurs ivoiriens. Conseils business, opportunités de financement et accompagnement personnalisé.'); ?>">
    <meta property="og:type" content="<?php echo $__env->yieldContent('og_type', 'website'); ?>">
    <meta property="og:url" content="<?php echo $__env->yieldContent('og_url', url()->current()); ?>">
    <meta property="og:image" content="<?php echo $__env->yieldContent('og_image', asset('images/lagento-social-preview.jpg')); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="fr_CI">
    <meta property="og:locale:alternate" content="fr_FR">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@LAgentO_CI">
    <meta name="twitter:creator" content="@LamBarro">
    <meta name="twitter:title" content="<?php echo $__env->yieldContent('twitter_title', 'LAgentO - Assistant IA Entrepreneurial'); ?>">
    <meta name="twitter:description" content="<?php echo $__env->yieldContent('twitter_description', 'Assistant IA pour entrepreneurs ivoiriens'); ?>">
    <meta name="twitter:image" content="<?php echo $__env->yieldContent('twitter_image', asset('images/lagento-social-preview.jpg')); ?>">
    
    <!-- Additional Meta Tags -->
    <meta name="theme-color" content="#ff6b35">
    <meta name="msapplication-TileColor" content="#ff6b35">
    <meta name="application-name" content="LAgentO">
    <meta name="apple-mobile-web-app-title" content="LAgentO">
    <meta name="format-detection" content="telephone=no">
    
    <!-- Preconnect for Performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon-light.png" id="favicon">
    <link rel="apple-touch-icon" href="/favicon-light.png">
    <link rel="manifest" href="/site.webmanifest">
    
    <?php if (! empty(trim($__env->yieldContent('schema_org')))): ?>
    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
        <?php echo $__env->yieldContent('schema_org'); ?>
    </script>
    <?php endif; ?>
    
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

        <!-- Suggestions tooltip flottant (détaché du chat) -->
        <?php if(auth()->guard()->check()): ?>
        <div x-data="suggestions()" 
                     x-show="showSuggestions" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="fixed bottom-20 left-1/2 transform -translate-x-1/2 w-full max-w-sm rounded-xl shadow-xl p-3" 
                     style="background: var(--white); border: 1px solid var(--gray-200); display: none; z-index: 9999;"
                     @click.away="showSuggestions = false">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium" style="color: var(--gray-600);">Suggestions</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" 
                                    @click="refreshSuggestions()" 
                                    :disabled="isRefreshing"
                                    class="p-1 rounded transition-colors" 
                                    style="color: var(--gray-600);" 
                                    onmouseover="if (!this.disabled) this.style.background='var(--gray-100)'" 
                                    onmouseout="this.style.background='transparent'" 
                                    title="Rafraîchir">
                                <i data-lucide="refresh-cw" class="w-3 h-3" :class="{ 'smooth-spin': isRefreshing }"></i>
                            </button>
                            <button type="button" 
                                    @click="showSuggestions = false" 
                                    class="p-1 rounded transition-colors" 
                                    style="color: var(--gray-600);" 
                                    onmouseover="this.style.background='var(--gray-100)'" 
                                    onmouseout="this.style.background='transparent'" 
                                    title="Fermer">
                                <i data-lucide="x" class="w-3 h-3"></i>
                            </button>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div x-show="isLoading" class="flex items-center justify-center py-4">
                            <i data-lucide="loader-2" class="w-4 h-4 smooth-spin" style="color: var(--gray-500);"></i>
                            <span class="ml-2 text-sm shimmer-text">Génération de suggestions...</span>
                        </div>
                        <template x-for="suggestion in suggestionsList" :key="suggestion">
                            <button type="button" 
                                    @click="selectSuggestion(suggestion)"
                                    class="w-full text-left px-3 py-2 text-sm rounded-md transition-colors" 
                                    style="color: var(--gray-900);" 
                                    onmouseover="this.style.background='var(--gray-100)'" 
                                    onmouseout="this.style.background='transparent'"
                                    x-text="suggestion">
                            </button>
                        </template>
                        <div x-show="!isLoading && suggestionsList.length === 0" class="text-center py-4">
                            <p class="text-sm" style="color: var(--gray-500);">Aucune suggestion disponible</p>
                            <button type="button" 
                                    @click="refreshSuggestions()"
                                    class="mt-2 text-xs px-3 py-1 rounded-md transition-colors"
                                    style="background: var(--orange-100); color: var(--orange-700);"
                                    onmouseover="this.style.background='var(--orange-200)'"
                                    onmouseout="this.style.background='var(--orange-100)'">
                                Générer des suggestions
                            </button>
                        </div>
                    </div>
                </div>
        <?php endif; ?>
        
        <!-- Chat fixe -->
        <?php if(auth()->guard()->check()): ?>
        <div class="fixed bottom-0 left-0 right-0 z-sticky" 
             style="background: var(--white); border-top: 1px solid var(--gray-200);" x-data="fixedChat()">
            <div class="container max-w-4xl mx-auto p-4">
                <!-- Modern chat input container -->
                <form @submit.prevent="sendMessage" class="relative">
                    <div class="relative rounded-2xl shadow-sm transition-all duration-200" style="background: var(--white); border: 1px solid var(--gray-200);" 
                         onfocusin="this.style.borderColor='var(--orange)'; this.style.boxShadow='0 0 0 3px rgba(255, 107, 53, 0.1)'" 
                         onfocusout="this.style.borderColor='var(--gray-200)'; this.style.boxShadow='none'">
                        
                        <!-- Aperçu fichier -->
                        <div x-show="attachedFile" class="absolute bottom-full mb-2 p-3 rounded-lg shadow-lg max-w-sm" style="background: var(--white); border: 1px solid var(--gray-200);">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: var(--orange-50);">
                                    <template x-if="attachedFile?.type?.startsWith('image/')">
                                        <i data-lucide="image" class="w-5 h-5" style="color: var(--orange-primary);"></i>
                                    </template>
                                    <template x-if="attachedFile?.type?.includes('pdf')">
                                        <i data-lucide="file-text" class="w-5 h-5" style="color: var(--red-500);"></i>
                                    </template>
                                    <template x-if="attachedFile?.type?.includes('word') || attachedFile?.name?.endsWith('.doc') || attachedFile?.name?.endsWith('.docx')">
                                        <i data-lucide="file-text" class="w-5 h-5" style="color: var(--blue-500);"></i>
                                    </template>
                                    <template x-if="!attachedFile?.type?.startsWith('image/') && !attachedFile?.type?.includes('pdf') && !attachedFile?.type?.includes('word') && !attachedFile?.name?.endsWith('.doc') && !attachedFile?.name?.endsWith('.docx')">
                                        <i data-lucide="file" class="w-5 h-5" style="color: var(--gray-600);"></i>
                                    </template>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium truncate" style="color: var(--gray-900);" x-text="attachedFile?.name"></div>
                                    <div class="text-xs" style="color: var(--gray-500);" x-text="formatFileSize(attachedFile?.size)"></div>
                                </div>
                                <button type="button" @click="removeAttachment()" class="p-1 rounded transition-colors" style="color: var(--gray-400);" onmouseover="this.style.background='var(--gray-100)'; this.style.color='var(--gray-600)'" onmouseout="this.style.background='transparent'; this.style.color='var(--gray-400)'">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
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
                                    accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif,.webp"
                                    class="hidden"
                                />
                                <button type="button" @click="$refs.fileInput.click()" 
                                        class="p-1.5 rounded-lg transition-all relative" 
                                        :style="attachedFile ? 'color: var(--orange-primary); background: var(--orange-50);' : 'color: var(--gray-600);'"
                                        onmouseover="if (!this.querySelector('.attached-indicator')) { this.style.color='var(--gray-900)'; this.style.background='var(--gray-100)'; }" 
                                        onmouseout="if (!this.querySelector('.attached-indicator')) { this.style.color='var(--gray-600)'; this.style.background='transparent'; }"
                                        title="Joindre un fichier (PDF, DOC, images)">
                                    <i data-lucide="paperclip" class="w-4 h-4"></i>
                                    <div x-show="attachedFile" class="attached-indicator absolute -top-1 -right-1 w-2 h-2 rounded-full" style="background: var(--orange-primary);"></div>
                                </button>
                                <button type="button" 
                                        @click="toggleSuggestions()"
                                        class="p-1.5 rounded-lg transition-all" 
                                        style="color: var(--gray-600);" 
                                        onmouseover="this.style.color='var(--gray-900)'; this.style.background='var(--gray-100)'" 
                                        onmouseout="this.style.color='var(--gray-600)'; this.style.background='transparent'"
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
                                <i data-lucide="loader-2" class="w-4 h-4 smooth-spin" x-show="isLoading"></i>
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
                
                // Si on n'est pas sur la page chat, stocker le message et rediriger
                const currentPath = window.location.pathname;
                if (currentPath !== '/chat') {
                    // Stocker le message dans sessionStorage
                    sessionStorage.setItem('pendingMessage', userMessage);
                    if (file) {
                        // Pour les fichiers, on devra les gérer différemment
                        sessionStorage.setItem('pendingFileAlert', 'true');
                    }
                    
                    // Créer une nouvelle conversation et rediriger
                    fetch('/chat/conversations/create', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Marquer qu'on vient de créer une nouvelle conversation avec un message en attente
                            sessionStorage.setItem('autoSubmitMessage', 'true');
                            window.location.href = data.redirect_url;
                        }
                    });
                    return;
                }
                
                // Code existant pour la page chat
                // Utiliser la méthode du chat component pour éviter la duplication
                const chatComponent = document.querySelector('[x-data*="chatInterface"]');
                if (chatComponent && chatComponent._x_dataStack) {
                    const chatData = chatComponent._x_dataStack[0];
                    
                    // Utiliser la méthode sendDirectMessage du chat component
                    this.message = '';
                    this.attachedFile = null;
                    this.isLoading = false;
                    
                    // Déléguer au composant chat principal
                    chatData.sendDirectMessage(userMessage);
                    return; // Sortir tôt pour éviter la duplication
                }
                
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
                .then(data => {
                    if (data.success) {
                        // Si on n'est pas sur la page chat ou si le composant chat n'existe pas, rediriger
                        window.location.href = '<?php echo e(route("chat.index")); ?>?conversation=' + data.conversation_id;
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    if (typeof window.showErrorToast === 'function') {
                        window.showErrorToast('Erreur lors de l\'envoi du message');
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                    // Arrêter le typing sur la page chat si présente
                    const chatComponent = document.querySelector('[x-data*="chatInterface"]');
                    if (chatComponent && chatComponent._x_dataStack) {
                        chatComponent._x_dataStack[0].isTyping = false;
                    }
                });
            },
            
            handleFileUpload(event) {
                const file = event.target.files[0];
                if (file && file.size <= 5 * 1024 * 1024) {
                    this.attachedFile = file;
                } else if (file) {
                    if (typeof window.showWarningToast === 'function') {
                        window.showWarningToast('Le fichier ne doit pas dépasser 5MB');
                    }
                    event.target.value = '';
                }
            },
            
            removeAttachment() {
                this.attachedFile = null;
                this.$refs.fileInput.value = '';
            },
            
            toggleSuggestions() {
                // Déclencher l'affichage du composant suggestions
                const suggestionsComponent = document.querySelector('[x-data*="suggestions"]');
                if (suggestionsComponent && suggestionsComponent._x_dataStack) {
                    const suggestionsData = suggestionsComponent._x_dataStack[0];
                    if (!suggestionsData.showSuggestions) {
                        suggestionsData.showSuggestions = true;
                        // Charger les suggestions si pas encore chargées
                        if (suggestionsData.suggestionsList.length === 0 && !suggestionsData.isLoading) {
                            suggestionsData.loadSuggestions();
                        }
                    } else {
                        suggestionsData.showSuggestions = false;
                    }
                }
            },
            
            formatFileSize(size) {
                if (!size) return '';
                if (size < 1024) return size + ' B';
                if (size < 1024 * 1024) return (size / 1024).toFixed(1) + ' KB';
                if (size < 1024 * 1024 * 1024) return (size / (1024 * 1024)).toFixed(1) + ' MB';
                return (size / (1024 * 1024 * 1024)).toFixed(1) + ' GB';
            }
        }
    }
    
    // Composant Alpine.js pour les suggestions
    function suggestions() {
        return {
            showSuggestions: false,
            suggestionsList: [],
            isLoading: false,
            isRefreshing: false,
            isCached: false,
            isRefreshed: false,
            refreshInterval: null,
            
            init() {
                // Rafraîchissement automatique toutes les 5 minutes
                this.refreshInterval = setInterval(() => {
                    if (this.suggestionsList.length > 0 && !this.isLoading && !this.isRefreshing) {
                        this.refreshSuggestions();
                    }
                }, 5 * 60 * 1000); // 5 minutes
            },
            
            destroy() {
                if (this.refreshInterval) {
                    clearInterval(this.refreshInterval);
                }
            },
            
            async loadSuggestions() {
                if (this.isLoading) return;
                
                this.isLoading = true;
                this.isRefreshed = false;
                
                try {
                    const response = await fetch('<?php echo e(route("chat.suggestions")); ?>?active_page=' + encodeURIComponent(window.location.pathname));
                    const data = await response.json();
                    
                    if (data.success && Array.isArray(data.suggestions)) {
                        this.suggestionsList = data.suggestions;
                        this.isCached = data.cached || false;
                        this.isRefreshed = false;
                        
                        // Afficher temporairement l'indicateur de cache/nouveau
                        if (this.isCached) {
                            setTimeout(() => {
                                this.isCached = false;
                            }, 3000);
                        }
                    } else {
                        console.warn('Réponse inattendue pour les suggestions:', data);
                        this.suggestionsList = [];
                        this.isCached = false;
                    }
                } catch (error) {
                    console.error('Erreur lors du chargement des suggestions:', error);
                    this.suggestionsList = [];
                    this.isCached = false;
                } finally {
                    this.isLoading = false;
                    // Réinitialiser les icônes après le chargement
                    this.$nextTick(() => {
                        if (typeof window.renderIcons === 'function') {
                            window.renderIcons();
                        }
                    });
                }
            },
            
            async refreshSuggestions() {
                if (this.isRefreshing) return;
                
                this.isRefreshing = true;
                this.isCached = false;
                this.isRefreshed = false;
                
                try {
                    const response = await fetch('<?php echo e(route("chat.suggestions.refresh")); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({
                            active_page: window.location.pathname
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success && Array.isArray(data.suggestions)) {
                        this.suggestionsList = data.suggestions;
                        this.isCached = false;
                        this.isRefreshed = data.refreshed || true;
                        
                        // Afficher temporairement l'indicateur "Nouvelles"
                        setTimeout(() => {
                            this.isRefreshed = false;
                        }, 5000);
                    } else {
                        console.warn('Réponse inattendue pour le rafraîchissement:', data);
                    }
                } catch (error) {
                    console.error('Erreur lors du rafraîchissement des suggestions:', error);
                } finally {
                    this.isRefreshing = false;
                    // Réinitialiser les icônes après le rafraîchissement
                    this.$nextTick(() => {
                        if (typeof window.renderIcons === 'function') {
                            window.renderIcons();
                        }
                    });
                }
            },
            
            selectSuggestion(suggestion) {
                // Fermer les suggestions
                this.showSuggestions = false;
                
                // Si on est sur la page chat, utiliser sendQuickMessage
                const chatComponent = document.querySelector('[x-data*="chatInterface"]');
                if (chatComponent && chatComponent._x_dataStack) {
                    const chatData = chatComponent._x_dataStack[0];
                    chatData.sendQuickMessage(suggestion);
                    return;
                }
                
                // Sinon, utiliser le chat fixe
                const fixedChatComponent = document.querySelector('[x-data*="fixedChat"]');
                if (fixedChatComponent && fixedChatComponent._x_dataStack) {
                    const fixedData = fixedChatComponent._x_dataStack[0];
                    fixedData.message = suggestion;
                    fixedData.sendMessage();
                } else {
                    // Fallback: remplir le textarea
                    const textarea = document.querySelector('textarea');
                    if (textarea) {
                        textarea.value = suggestion;
                        textarea.focus();
                        textarea.dispatchEvent(new Event('input'));
                    }
                }
            }
        }
    }
    
    // Fonctions pour les conversations  
    function closeConversation(button) {
        const tab = button.closest('.conversation-tab');
        tab.remove();
    }
    
    function createNewConversation() {
        // Marquer qu'on crée une nouvelle conversation
        sessionStorage.setItem('newConversationCreated', 'true');
        
        // Activer les suggestions avant la redirection si on est déjà sur la page chat
        const currentPath = window.location.pathname;
        if (currentPath === '<?php echo e(route("chat.index")); ?>' || currentPath.includes('/chat')) {
            const suggestionsComponent = document.querySelector('[x-data*="suggestions"]');
            if (suggestionsComponent && suggestionsComponent._x_dataStack) {
                const suggestionsData = suggestionsComponent._x_dataStack[0];
                suggestionsData.showSuggestions = true;
                if (suggestionsData.suggestionsList.length === 0 && !suggestionsData.isLoading) {
                    suggestionsData.loadSuggestions();
                }
            }
        }
        window.location.href = '<?php echo e(route("chat.index")); ?>';
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
    
    <!-- Global Toast Notifications -->
    <?php if (isset($component)) { $__componentOriginal7cfab914afdd05940201ca0b2cbc009b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.toast','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $attributes = $__attributesOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__attributesOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b)): ?>
<?php $component = $__componentOriginal7cfab914afdd05940201ca0b2cbc009b; ?>
<?php unset($__componentOriginal7cfab914afdd05940201ca0b2cbc009b); ?>
<?php endif; ?>
</body>
</html>
<?php /**PATH /Users/laminebarro/agent-O/resources/views/layouts/app.blade.php ENDPATH**/ ?>