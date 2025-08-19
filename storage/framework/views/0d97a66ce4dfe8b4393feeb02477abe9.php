<!-- Global Toast Notification System -->
<div x-data="toastNotifications()" 
     x-init="init()" 
     class="fixed top-4 right-4 z-50 pointer-events-none"
     style="z-index: 9999;">
    
    <!-- Toast Container -->
    <div class="space-y-3">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.show"
                 x-transition:enter="transform transition ease-out duration-300"
                 x-transition:enter-start="translate-x-full opacity-0"
                 x-transition:enter-end="translate-x-0 opacity-100"
                 x-transition:leave="transform transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0 opacity-100"
                 x-transition:leave-end="translate-x-full opacity-0"
                 class="toast-notification pointer-events-auto relative w-80 rounded-lg shadow-lg overflow-hidden"
                 :class="{
                     'bg-white border border-gray-200': toast.type === 'info',
                     'bg-green-50 border border-green-200': toast.type === 'success',
                     'bg-red-50 border border-red-200': toast.type === 'error',
                     'bg-yellow-50 border border-yellow-200': toast.type === 'warning'
                 }">
                
                <!-- Progress Bar -->
                <div x-show="toast.showProgress && toast.progress >= 0"
                     class="absolute top-0 left-0 h-1 transition-all duration-100 ease-linear"
                     :class="{
                         'bg-blue-500': toast.type === 'info',
                         'bg-green-500': toast.type === 'success',
                         'bg-red-500': toast.type === 'error',
                         'bg-yellow-500': toast.type === 'warning'
                     }"
                     :style="`width: ${toast.progress}%`">
                </div>
                
                <!-- Toast Content -->
                <div class="p-4">
                    <div class="flex items-start">
                        <!-- Icon -->
                        <div class="flex-shrink-0 mr-3">
                            <template x-if="toast.type === 'success'">
                                <div class="w-5 h-5 text-green-600">
                                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                                </div>
                            </template>
                            <template x-if="toast.type === 'error'">
                                <div class="w-5 h-5 text-red-600">
                                    <i data-lucide="x-circle" class="w-5 h-5"></i>
                                </div>
                            </template>
                            <template x-if="toast.type === 'warning'">
                                <div class="w-5 h-5 text-yellow-600">
                                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                                </div>
                            </template>
                            <template x-if="toast.type === 'info'">
                                <div class="w-5 h-5 text-blue-600">
                                    <i data-lucide="info" class="w-5 h-5"></i>
                                </div>
                            </template>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <h4 x-show="toast.title" 
                                x-text="toast.title" 
                                class="text-sm font-medium mb-1"
                                :class="{
                                    'text-gray-900': toast.type === 'info',
                                    'text-green-900': toast.type === 'success',
                                    'text-red-900': toast.type === 'error',
                                    'text-yellow-900': toast.type === 'warning'
                                }">
                            </h4>
                            <p x-text="toast.message" 
                               class="text-sm"
                               :class="{
                                   'text-gray-700': toast.type === 'info',
                                   'text-green-700': toast.type === 'success',
                                   'text-red-700': toast.type === 'error',
                                   'text-yellow-700': toast.type === 'warning'
                               }">
                            </p>
                        </div>
                        
                        <!-- Close Button -->
                        <div class="flex-shrink-0 ml-3">
                            <button @click="removeToast(toast.id)" 
                                    class="inline-flex rounded-md p-1.5 hover:bg-gray-100 focus:outline-none transition-colors"
                                    :class="{
                                        'text-gray-400 hover:text-gray-600': toast.type === 'info',
                                        'text-green-400 hover:text-green-600': toast.type === 'success',
                                        'text-red-400 hover:text-red-600': toast.type === 'error',
                                        'text-yellow-400 hover:text-yellow-600': toast.type === 'warning'
                                    }">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function toastNotifications() {
    return {
        toasts: [],
        toastId: 0,
        
        init() {
            // Exposer les méthodes globalement
            window.showToast = this.showToast.bind(this);
            window.showSuccessToast = this.showSuccessToast.bind(this);
            window.showErrorToast = this.showErrorToast.bind(this);
            window.showWarningToast = this.showWarningToast.bind(this);
            window.showInfoToast = this.showInfoToast.bind(this);
            
            // Initialiser les icônes
            this.$nextTick(() => {
                if (typeof window.renderIcons === 'function') {
                    window.renderIcons();
                }
            });
        },
        
        showToast(type, message, title = '', duration = 5000, showProgress = true) {
            const id = ++this.toastId;
            const toast = {
                id,
                type,
                message,
                title,
                show: false,
                progress: showProgress ? 100 : -1,
                showProgress
            };
            
            this.toasts.push(toast);
            
            // Afficher le toast après un tick
            this.$nextTick(() => {
                toast.show = true;
                
                // Rafraîchir les icônes
                setTimeout(() => {
                    if (typeof window.renderIcons === 'function') {
                        window.renderIcons();
                    }
                }, 100);
                
                // Démarrer la barre de progression si activée
                if (showProgress && duration > 0) {
                    this.startProgressBar(toast, duration);
                }
                
                // Auto-remove après la durée spécifiée
                if (duration > 0) {
                    setTimeout(() => {
                        this.removeToast(id);
                    }, duration);
                }
            });
            
            return id;
        },
        
        startProgressBar(toast, duration) {
            const interval = 50; // Update every 50ms
            const steps = duration / interval;
            const progressStep = 100 / steps;
            
            const progressInterval = setInterval(() => {
                if (toast.progress <= 0) {
                    clearInterval(progressInterval);
                    return;
                }
                
                toast.progress -= progressStep;
                
                if (toast.progress <= 0) {
                    toast.progress = 0;
                    clearInterval(progressInterval);
                }
            }, interval);
        },
        
        removeToast(id) {
            const toastIndex = this.toasts.findIndex(t => t.id === id);
            if (toastIndex > -1) {
                this.toasts[toastIndex].show = false;
                
                // Supprimer complètement après l'animation
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            }
        },
        
        // Méthodes de convenance
        showSuccessToast(message, title = 'Succès') {
            return this.showToast('success', message, title);
        },
        
        showErrorToast(message, title = 'Erreur') {
            return this.showToast('error', message, title);
        },
        
        showWarningToast(message, title = 'Attention') {
            return this.showToast('warning', message, title);
        },
        
        showInfoToast(message, title = 'Information') {
            return this.showToast('info', message, title);
        }
    }
}
</script><?php /**PATH /Users/laminebarro/agent-O/resources/views/components/toast.blade.php ENDPATH**/ ?>