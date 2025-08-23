<!-- Guest Chat Widget -->
<div id="guestChatWidget" x-data="guestChat()" x-init="init()" class="fixed bottom-4 right-4 z-50">
    <!-- Chat Button -->
    <button 
        x-show="!isOpen" 
        @click="toggleChat()"
        class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-full p-4 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-110"
        style="background: linear-gradient(135deg, #ff6b35 0%, #f77737 100%);"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
        </svg>
        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center animate-pulse">!</span>
    </button>

    <!-- Chat Window -->
    <div 
        x-show="isOpen" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="bg-white rounded-lg shadow-2xl w-[90vw] sm:w-96 h-[80vh] sm:h-[600px] max-h-[600px] flex flex-col fixed bottom-20 right-4 sm:bottom-4 sm:right-4"
        style="box-shadow: 0 20px 60px -15px rgba(0, 0, 0, 0.3);"
    >
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white p-4 rounded-t-lg flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold">Testez LagentO</h3>
                    <p class="text-xs opacity-90">Assistant IA - Mode découverte</p>
                </div>
            </div>
            <button @click="toggleChat()" class="text-white/80 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Messages Container -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3" x-ref="messagesContainer">
            <!-- Welcome Message -->
            <div class="flex items-start gap-2">
                <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-orange-600 text-sm font-bold">L</span>
                </div>
                <div class="bg-gray-100 rounded-lg p-3 max-w-[80%]">
                    <p class="text-sm text-gray-700">
                        👋 Bonjour ! Je suis LagentO, votre assistant IA entrepreneurial. 
                        Je peux vous informer sur les opportunités de financement disponibles en Côte d'Ivoire.
                    </p>
                    <p class="text-xs text-gray-500 mt-2">
                        💡 Mode découverte : Inscrivez-vous pour accéder à toutes les fonctionnalités.
                    </p>
                </div>
            </div>

            <!-- Dynamic Messages -->
            <template x-for="message in messages" :key="message.id">
                <div class="flex items-start gap-2" :class="message.role === 'user' ? 'justify-end' : ''">
                    <div x-show="message.role === 'assistant'" class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-orange-600 text-sm font-bold">L</span>
                    </div>
                    <div 
                        class="rounded-lg p-3 max-w-[80%]"
                        :class="message.role === 'user' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-700'"
                    >
                        <p class="text-sm whitespace-pre-wrap" x-html="message.content"></p>
                    </div>
                </div>
            </template>

            <!-- Loading indicator -->
            <div x-show="isLoading" class="flex items-start gap-2">
                <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-orange-600 text-sm font-bold">L</span>
                </div>
                <div class="bg-gray-100 rounded-lg p-3">
                    <div class="flex gap-1">
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms;"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms;"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms;"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="border-t p-4">
            <form @submit.prevent="sendMessage()" class="flex gap-2">
                <input 
                    type="text" 
                    x-model="newMessage"
                    :disabled="isLoading"
                    placeholder="Posez votre question..."
                    class="flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm"
                    maxlength="500"
                />
                <button 
                    type="submit"
                    :disabled="isLoading || !newMessage.trim()"
                    class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
            <p class="text-xs text-gray-500 mt-2 text-center">
                <a href="#" @click.prevent="scrollToForm()" class="text-orange-500 hover:underline">
                    Inscrivez-vous pour plus de fonctionnalités →
                </a>
            </p>
        </div>
    </div>
</div>

<script>
function guestChat() {
    return {
        isOpen: false,
        messages: [],
        newMessage: '',
        isLoading: false,
        messageIdCounter: 1,

        init() {
            // Auto-open after 3 seconds on first visit
            if (!localStorage.getItem('guestChatShown')) {
                setTimeout(() => {
                    this.isOpen = true;
                    localStorage.setItem('guestChatShown', 'true');
                }, 3000);
            }
        },

        toggleChat() {
            this.isOpen = !this.isOpen;
        },

        async sendMessage() {
            if (!this.newMessage.trim() || this.isLoading) return;

            const userMessage = this.newMessage;
            this.newMessage = '';
            
            // Add user message
            this.messages.push({
                id: this.messageIdCounter++,
                role: 'user',
                content: userMessage
            });

            this.isLoading = true;
            this.scrollToBottom();

            try {
                const response = await fetch('/api/guest-chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ message: userMessage })
                });

                const data = await response.json();

                if (data.success) {
                    // Add assistant response
                    this.messages.push({
                        id: this.messageIdCounter++,
                        role: 'assistant',
                        content: this.formatMessage(data.response)
                    });
                } else {
                    this.messages.push({
                        id: this.messageIdCounter++,
                        role: 'assistant',
                        content: 'Désolé, une erreur est survenue. Veuillez réessayer.'
                    });
                }
            } catch (error) {
                console.error('Chat error:', error);
                this.messages.push({
                    id: this.messageIdCounter++,
                    role: 'assistant',
                    content: 'Erreur de connexion. Veuillez réessayer.'
                });
            } finally {
                this.isLoading = false;
                this.scrollToBottom();
            }
        },

        formatMessage(text) {
            // Convert markdown bold to HTML
            return text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        },

        scrollToBottom() {
            this.$nextTick(() => {
                this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
            });
        },

        scrollToForm() {
            this.isOpen = false;
            const form = document.querySelector('form[action*="auth.email"]');
            if (form) {
                form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Focus on email input
                setTimeout(() => {
                    const emailInput = form.querySelector('input[type="email"]');
                    if (emailInput) emailInput.focus();
                }, 500);
            }
        }
    }
}
</script><?php /**PATH /Users/laminebarro/agent-O/resources/views/components/guest-chat.blade.php ENDPATH**/ ?>