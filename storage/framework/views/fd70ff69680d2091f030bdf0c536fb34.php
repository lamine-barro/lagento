<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    
    <!-- SEO Meta Tags -->
    <title><?php echo $__env->yieldContent('seo_title', '@yield('title', 'Dashboard') - LAgentO Assistant IA Entrepreneurial'); ?></title>
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', 'Tableau de bord LAgentO : Gérez vos projets entrepreneuriaux, consultez votre assistant IA et accédez aux opportunités en Côte d\'Ivoire.'); ?>">
    <meta name="keywords" content="<?php echo $__env->yieldContent('meta_keywords', 'dashboard entrepreneur, projets startup, assistant IA, gestion entreprise côte ivoire'); ?>">
    <meta name="robots" content="<?php echo $__env->yieldContent('meta_robots', 'noindex, nofollow'); ?>">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo $__env->yieldContent('og_title', '@yield('title', 'Dashboard') - LAgentO'); ?>">
    <meta property="og:description" content="<?php echo $__env->yieldContent('og_description', '@yield('meta_description', 'Tableau de bord LAgentO pour entrepreneurs ivoiriens')'); ?>">
    <meta property="og:url" content="<?php echo e(url()->current()); ?>">
    <meta property="og:image" content="<?php echo $__env->yieldContent('og_image', asset('images/lagento-dashboard.jpg')); ?>">
    <meta property="og:site_name" content="LAgentO">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo $__env->yieldContent('twitter_title', '@yield('title', 'Dashboard') - LAgentO'); ?>">
    <meta name="twitter:description" content="<?php echo $__env->yieldContent('twitter_description', '@yield('meta_description', 'Tableau de bord LAgentO pour entrepreneurs')'); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon.png">
    
    
    
    <!-- Styles -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    
    <?php echo $__env->yieldPushContent('styles'); ?>
    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<body x-data="{ sidebarOpen: false, currentView: '<?php echo e(request()->routeIs('chat') ? 'agent' : 'dashboard'); ?>' }">
    <div class="min-h-screen dashboard">
        <!-- Navbar -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white border-b" style="border-color: var(--gray-100); height: 60px;">
            <div class="flex items-center justify-between h-full px-4">
                <!-- Hamburger Menu -->
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="menu" class="w-5 h-5" style="color: var(--gray-700);"></i>
                </button>
                
                <!-- Brand -->
                <div class="absolute left-1/2 transform -translate-x-1/2">
                    <h1 class="text-xl font-bold" style="font-family: 'Poppins', sans-serif; color: var(--gray-900);">
                        LAgent<span style="color: var(--orange-primary);">O</span>
                    </h1>
                </div>
                
                <!-- Toggle Switch -->
                <div class="flex items-center bg-gray-100 rounded-lg p-1">
                    <button 
                        @click="currentView = 'dashboard'; window.location.href = '<?php echo e(route('dashboard')); ?>'"
                        :class="currentView === 'dashboard' ? 'bg-white shadow-sm' : ''"
                        class="px-3 py-2 text-sm font-medium rounded-md transition-all"
                        :style="currentView === 'dashboard' ? 'color: var(--gray-900);' : 'color: var(--gray-600);'"
                    >
                        <i data-lucide="bar-chart-3" class="w-4 h-4 mr-1.5"></i>
                        Dashboard
                    </button>
                    <button 
                        @click="currentView = 'agent'; window.location.href = '<?php echo e(route('chat')); ?>'"
                        :class="currentView === 'agent' ? 'bg-white shadow-sm' : ''"
                        class="px-3 py-2 text-sm font-medium rounded-md transition-all"
                        :style="currentView === 'agent' ? 'color: var(--gray-900);' : 'color: var(--gray-600);'"
                    >
                        <i data-lucide="message-square" class="w-4 h-4 mr-1.5"></i>
                        Agent
                    </button>
                </div>
            </div>
        </nav>

        <!-- Sidebar -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-black bg-opacity-50"
             style="display: none;">
        </div>

        <div x-show="sidebarOpen"
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed top-0 left-0 z-50 w-64 h-full bg-white shadow-lg"
             style="display: none;">
            
            <div class="flex items-center justify-between p-4 border-b" style="border-color: var(--gray-100);">
                <h2 class="text-lg font-semibold" style="color: var(--gray-900); font-family: 'Poppins', sans-serif;">
                    Menu
                </h2>
                <button @click="sidebarOpen = false" class="p-1 hover:bg-gray-100 rounded">
                    <i data-lucide="x" class="w-5 h-5" style="color: var(--gray-500);"></i>
                </button>
            </div>

            <div class="p-4 space-y-2">
                <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center p-3 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors" style="color: var(--gray-700);">
                    <i data-lucide="bar-chart-3" class="w-5 h-5 mr-3"></i>
                    Dashboard
                </a>
                
                <a href="<?php echo e(route('chat')); ?>" class="flex items-center p-3 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors" style="color: var(--gray-700);">
                    <i data-lucide="message-square" class="w-5 h-5 mr-3"></i>
                    Agent Chat
                </a>
                
                <a href="<?php echo e(route('conversations.index')); ?>" class="flex items-center p-3 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors" style="color: var(--gray-700);">
                    <i data-lucide="message-circle" class="w-5 h-5 mr-3"></i>
                    Conversations
                </a>
                
                <a href="<?php echo e(route('profile')); ?>" class="flex items-center p-3 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors" style="color: var(--gray-700);">
                    <i data-lucide="user" class="w-5 h-5 mr-3"></i>
                    Profil
                </a>
                
                <div class="border-t pt-2 mt-4" style="border-color: var(--gray-200);">
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="flex items-center w-full p-3 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors" style="color: var(--gray-700);">
                            <i data-lucide="log-out" class="w-5 h-5 mr-3"></i>
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <main style="padding-top: 60px; padding-bottom: 100px;">
            <?php echo $__env->yieldContent('content'); ?>
        </main>

        <!-- Fixed Chat Form -->
        <?php if(auth()->guard()->check()): ?>
        <div class="fixed bottom-0 left-0 right-0 z-30 bg-white border-t" style="border-color: var(--gray-200);" x-data="fixedChat()">
            <div class="max-w-4xl mx-auto p-4">
                <form @submit.prevent="sendMessage" class="flex items-center gap-3">
                    <!-- Attachment Button -->
                    <button 
                        type="button"
                        @click="$refs.fileInput.click()"
                        class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                        title="Joindre un fichier"
                    >
                        <i data-lucide="paperclip" class="w-5 h-5" style="color: var(--gray-500);"></i>
                    </button>
                    
                    <input 
                        type="file" 
                        x-ref="fileInput" 
                        @change="handleFileUpload"
                        accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png"
                        style="display: none;"
                    />
                    
                    <!-- Input Container -->
                    <div class="flex-1 relative">
                        <!-- Attached File Preview -->
                        <div x-show="attachedFile" class="mb-2 p-2 border rounded-lg flex items-center gap-2" style="border-color: var(--gray-200); background: var(--gray-50);">
                            <i data-lucide="file" class="w-4 h-4" style="color: var(--gray-500);"></i>
                            <span x-text="attachedFile?.name" class="text-sm flex-1" style="color: var(--gray-700);"></span>
                            <button type="button" @click="removeAttachment()" class="p-1">
                                <i data-lucide="x" class="w-4 h-4" style="color: var(--gray-500);"></i>
                            </button>
                        </div>
                        
                        <!-- Input Field -->
                        <div class="flex items-center border rounded-lg" style="border-color: var(--gray-300);">
                            <input 
                                x-model="message"
                                @keydown.enter.prevent="sendMessage"
                                placeholder="Posez votre question à LAgentO..."
                                class="flex-1 p-3 border-0 resize-none focus:outline-none rounded-l-lg"
                            />
                            
                            <!-- Send Button -->
                            <button 
                                type="submit"
                                :disabled="!message.trim() && !attachedFile || isLoading"
                                class="p-3 rounded-r-lg transition-colors"
                                :class="(!message.trim() && !attachedFile) || isLoading ? 'cursor-not-allowed' : 'cursor-pointer'"
                                :style="(!message.trim() && !attachedFile) || isLoading ? 'background: var(--gray-300); color: var(--gray-500);' : 'background: var(--orange-primary); color: white;'"
                            >
                                <i data-lucide="send" class="w-5 h-5"></i>
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
                
                // Create FormData for file upload
                const formData = new FormData();
                formData.append('message', userMessage);
                if (file) {
                    formData.append('file', file);
                }
                
                // Send to backend
                fetch('<?php echo e(route("chat.send")); ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Redirect to chat page to see the response
                    window.location.href = '<?php echo e(route("chat")); ?>';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erreur lors de l\'envoi du message');
                })
                .finally(() => {
                    this.isLoading = false;
                });
            },
            
            handleFileUpload(event) {
                const file = event.target.files[0];
                if (file) {
                    // Check file size (5MB limit)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('Le fichier ne doit pas dépasser 5MB');
                        return;
                    }
                    this.attachedFile = file;
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