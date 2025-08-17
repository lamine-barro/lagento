<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> - LAgentO</title>
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', 'Assistant IA entrepreneurial pour la Côte d\'Ivoire'); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/favicon.png">
    
    <!-- Styles -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body x-data="{ 
    sidebarOpen: false, 
    currentView: '<?php echo e(request()->routeIs('chat') ? 'agent' : 'dashboard'); ?>',
    closeSidebar() { this.sidebarOpen = false; }
}" class="bg-gray-50">
    <div class="page">
        <!-- Header -->
        <header class="fixed top-0 left-0 right-0 z-fixed bg-white border-b border-gray-200" style="height: var(--header-height);">
            <div class="container max-w-7xl mx-auto flex items-center justify-between h-full">
                <!-- Menu mobile -->
                <button 
                    @click="sidebarOpen = !sidebarOpen" 
                    class="touch-target rounded-lg hover:bg-gray-100 transition-colors md:hidden"
                    aria-label="Menu"
                >
                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-semibold text-gray-900">
                        LAgent<span class="text-orange">O</span>
                    </h1>
                </div>
                
                <!-- Navigation desktop -->
                <nav class="hidden md:flex items-center bg-gray-100 rounded-lg p-1">
                    <a 
                        href="<?php echo e(route('dashboard')); ?>"
                        class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-md transition-colors
                               <?php echo e(request()->routeIs('dashboard') ? 'bg-white shadow-sm text-gray-900' : 'text-gray-600 hover:text-gray-900'); ?>"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="hidden lg:inline">Dashboard</span>
                    </a>
                    <a 
                        href="<?php echo e(route('chat')); ?>"
                        class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-md transition-colors
                               <?php echo e(request()->routeIs('chat') ? 'bg-white shadow-sm text-gray-900' : 'text-gray-600 hover:text-gray-900'); ?>"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <span class="hidden lg:inline">Agent</span>
                    </a>
                </nav>
                
                <!-- Profil -->
                <div class="hidden md:block">
                    <a href="<?php echo e(route('profile')); ?>" class="touch-target rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </header>

        <!-- Sidebar mobile -->
        <div 
            x-show="sidebarOpen" 
            x-transition:enter="transition-opacity duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="closeSidebar()"
            class="fixed inset-0 z-overlay bg-black bg-opacity-50 md:hidden"
            style="display: none;"
        ></div>

        <aside 
            x-show="sidebarOpen"
            x-transition:enter="transition-transform duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition-transform duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed top-0 left-0 z-modal h-full bg-white shadow-lg md:hidden"
            style="width: var(--sidebar-width); display: none;"
        >
            <!-- Header sidebar -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Menu</h2>
                <button @click="closeSidebar()" class="touch-target rounded hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Navigation mobile -->
            <nav class="p-4 space-y-2">
                <a href="<?php echo e(route('dashboard')); ?>" @click="closeSidebar()" 
                   class="flex items-center gap-3 p-3 text-sm font-medium rounded-lg transition-colors <?php echo e(request()->routeIs('dashboard') ? 'bg-orange text-white' : 'text-gray-700 hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Dashboard
                </a>
                
                <a href="<?php echo e(route('chat')); ?>" @click="closeSidebar()" 
                   class="flex items-center gap-3 p-3 text-sm font-medium rounded-lg transition-colors <?php echo e(request()->routeIs('chat') ? 'bg-orange text-white' : 'text-gray-700 hover:bg-gray-100'); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    Agent Chat
                </a>
                
                <a href="<?php echo e(route('conversations.index')); ?>" @click="closeSidebar()" 
                   class="flex items-center gap-3 p-3 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-6a2 2 0 012-2h8z"></path>
                    </svg>
                    Conversations
                </a>
                
                <a href="<?php echo e(route('profile')); ?>" @click="closeSidebar()" 
                   class="flex items-center gap-3 p-3 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Profil
                </a>
                
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="flex items-center gap-3 w-full p-3 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors text-gray-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Déconnexion
                        </button>
                    </form>
                </div>
            </nav>
        </aside>
        
        <!-- Contenu principal -->
        <main class="main" style="padding-bottom: var(--chat-height);">
            <?php echo $__env->yieldContent('content'); ?>
        </main>

        <!-- Chat fixe -->
        <?php if(auth()->guard()->check()): ?>
        <div class="fixed bottom-0 left-0 right-0 z-sticky bg-white border-t border-gray-200" 
             style="height: var(--chat-height);" x-data="fixedChat()">
            <div class="container max-w-7xl mx-auto flex items-center gap-3 h-full">
                <!-- Bouton fichier -->
                <button 
                    type="button"
                    @click="$refs.fileInput.click()"
                    class="touch-target rounded-lg hover:bg-gray-100 transition-colors"
                    title="Joindre un fichier"
                >
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                    </svg>
                </button>
                
                <input 
                    type="file" 
                    x-ref="fileInput" 
                    @change="handleFileUpload"
                    accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png"
                    class="hidden"
                />
                
                <!-- Champ de saisie -->
                <form @submit.prevent="sendMessage" class="flex-1 flex gap-2">
                    <div class="flex-1 relative">
                        <!-- Aperçu fichier -->
                        <div x-show="attachedFile" class="absolute bottom-full mb-2 p-2 bg-gray-100 border border-gray-200 rounded-lg flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span x-text="attachedFile?.name" class="text-sm text-gray-700 flex-1"></span>
                            <button type="button" @click="removeAttachment()" class="p-1 hover:bg-gray-200 rounded">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <input 
                            x-model="message"
                            @keydown.enter.prevent="sendMessage"
                            placeholder="Posez votre question à LAgentO..."
                            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange focus:border-transparent"
                        />
                    </div>
                    
                    <!-- Bouton envoyer -->
                    <button 
                        type="submit"
                        :disabled="!message.trim() && !attachedFile || isLoading"
                        class="touch-target rounded-lg transition-colors"
                        :class="(!message.trim() && !attachedFile) || isLoading ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-orange text-white hover:bg-orange-light'"
                    >
                        <svg x-show="!isLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        <svg x-show="isLoading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
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
    </script>
    <?php endif; ?>
</body>
</html>
<?php /**PATH /Users/laminebarro/agent-O/resources/views/layouts/app.blade.php ENDPATH**/ ?>