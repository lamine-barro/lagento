@extends('layouts.app')

@section('title', 'Chat assistant IA')
@section('page_title', 'Chat avec assistant IA entrepreneurial - Conseils business 24/7 | Agento')
@section('seo_title', 'Chat avec assistant IA entrepreneurial - Conseils business 24/7 | Agento')
@section('meta_description', 'Chattez avec votre assistant IA entrepreneurial 24/7. Conseils business personnalisés, opportunités de financement, stratégie d\'entreprise et accompagnement pour entrepreneurs ivoiriens.')
@section('meta_keywords', 'chat assistant IA, conseil business 24/7, assistant entrepreneur, aide startup, conseil IA côte ivoire')
@section('meta_robots', 'noindex, nofollow')
@section('canonical_url', route('chat.index'))
@section('og_title', 'Chat assistant IA entrepreneurial - Agento')
@section('og_description', 'Obtenez des conseils business instantanés avec votre assistant IA entrepreneurial personnalisé.')
@section('schema_org')
@verbatim
{
    "@context": "https://schema.org",
    "@type": "SoftwareApplication",
    "name": "Chat Assistant IA Agento",
    "applicationCategory": "BusinessApplication",
    "description": "Assistant IA conversationnel pour entrepreneurs et startups",
    "operatingSystem": "Web Browser",
    "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "XOF"
    }
}
@endverbatim
@endsection

@section('content')
<div class="min-h-screen bg-white" x-data="chatInterface()" x-init="init()" data-conversation-id="{{ $conversation->id ?? '' }}">
    <!-- Chat Header with Tabs -->
    <div class="bg-white border-b p-4" style="border-color: var(--gray-100);">
        <div class="max-w-4xl mx-auto">
            <!-- Tabs conversations -->
            <div class="flex items-center overflow-x-auto scrollbar-hidden" id="conversationTabs" x-ref="tabsContainer" style="scroll-behavior: smooth; -webkit-overflow-scrolling: touch;">
                    <div class="flex items-center gap-2 flex-shrink-0">
                    <!-- Nouvelle conversation toujours en premier -->
                    <button type="button" class="new-conversation-tab flex items-center gap-1 px-4 py-2 rounded-md text-sm font-medium transition-colors whitespace-nowrap flex-shrink-0" style="background: var(--gray-50); color: var(--gray-600); border: 1px solid var(--gray-200); border-style: dashed;" onmouseover="this.style.background='var(--gray-100)'" onmouseout="this.style.background='var(--gray-50)'" @click="createNewConversation()">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Nouvelle conversation</span>
                    </button>
                    
                    <!-- Conversations dynamiques -->
                    <template x-for="conv in conversations" :key="conv.id">
                        <div class="conversation-tab flex items-center gap-1 px-4 py-2 rounded-md text-sm font-medium transition-colors whitespace-nowrap cursor-pointer" 
                             :style="conv.id === currentConversationId ? 'background: var(--orange-100); color: var(--orange-700); border: 1px solid var(--orange-200);' : 'background: var(--gray-100); color: var(--gray-700); border: 1px solid var(--gray-200);'"
                             @mouseover="if(conv.id !== currentConversationId) $el.style.background='var(--gray-200)'"
                             @mouseout="if(conv.id !== currentConversationId) $el.style.background='var(--gray-100)'"
                             @click="switchToConversation(conv.id)">
                            <span class="max-w-48 truncate" x-text="conv.title"></span>
                            <button type="button" 
                                    class="ml-2 p-0.5 rounded hover:bg-red-200 transition-colors" 
                                    @click.stop="confirmDeleteConversation(conv.id, conv.title)"
                                    x-show="conversations.length > 1"
                                    title="Supprimer la conversation">
                                <i data-lucide="x" class="w-3 h-3 text-red-600"></i>
                            </button>
                        </div>
                    </template>
                    </div>
            </div>
        </div>
    </div>

    <!-- Messages Area -->
    <div class="max-w-4xl mx-auto p-4 space-y-4" x-ref="messagesArea">
        
        <!-- Welcome Message -->
        <div x-show="messages.length === 0">
            <p class="text-sm" style="color: var(--gray-700);">Je suis LagentO, votre assistant IA entrepreneurial. Comment puis-je vous aider aujourd'hui ?</p>
        </div>
        
        <!-- Messages dynamiques -->
        <template x-for="message in messages" :key="message.id">
            <div>
                <!-- User Message -->
                <div x-show="message.role === 'user'" class="flex justify-end">
                    <div class="max-w-xs lg:max-w-lg xl:max-w-xl">
                        <div class="px-4 py-3 rounded-lg user-message" style="background: var(--orange-primary); color: white !important;">
                            <p class="text-sm" style="color: white !important;" x-text="message.content"></p>
                            <div class="text-xs mt-1" style="color: rgba(255, 255, 255, 0.8) !important;" x-text="formatTime(message.created_at)">
                            </div>
                        </div>
                        <!-- Fichier attaché -->
                        <div x-show="message.attachment" class="mt-2 p-3 rounded-lg border bg-white shadow-sm">
                            <div class="flex items-center gap-2">
                                <i data-lucide="paperclip" class="w-4 h-4" style="color: var(--gray-500);"></i>
                                <div class="flex-1">
                                    <div class="text-sm font-medium" style="color: var(--gray-900);" x-text="message.attachment?.name"></div>
                                    <div class="text-xs" style="color: var(--gray-500);" x-text="formatFileSize(message.attachment?.size)"></div>
                                </div>
                                <template x-if="message.attachment?.type?.startsWith('image/')">
                                    <img :src="message.attachment.url" :alt="message.attachment.name" class="w-8 h-8 rounded object-cover">
                                </template>
                                <template x-if="!message.attachment?.type?.startsWith('image/')">
                                    <div class="w-8 h-8 rounded flex items-center justify-center" style="background: var(--gray-100);">
                                        <i data-lucide="file" class="w-4 h-4" style="color: var(--gray-600);"></i>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Assistant Message -->
                <div x-show="message.role === 'assistant' && (message.content || !message.isStreaming)" class="flex items-start chat-message assistant">
                    <div class="flex-1">
                        <div class="prose prose-sm max-w-none" x-html="processMarkdown(message.content)"></div>
                        
                        <!-- Message Actions -->
                        <div class="flex items-center gap-2 mt-3 message-actions">
                            <button 
                                @click="copyMessage(message.content, $event)"
                                class="copy-button p-2 rounded-lg hover:bg-gray-100 transition-all duration-200 relative"
                                title="Copier"
                            >
                                <i data-lucide="copy" class="w-4 h-4" style="color: var(--gray-500);"></i>
                                <span class="copy-feedback absolute -top-8 left-1/2 transform -translate-x-1/2 px-2 py-1 text-xs rounded shadow-lg opacity-0 pointer-events-none transition-all duration-300" style="background: var(--gray-800); color: white; white-space: nowrap;">Copié!</span>
                            </button>
                            
                            <span class="text-xs ml-auto" style="color: var(--gray-500);" x-text="formatTime(message.created_at)">
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        
        <!-- Typing Indicator -->
        <div x-show="isTyping" class="flex items-start" style="display: none;">
            <div class="flex items-center gap-3 py-2">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-600 dark:text-orange-400 pulse-icon">
                        <path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2Z"/>
                        <path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2Z"/>
                    </svg>
                </div>
                <span class="text-sm shimmer-text font-medium thinking-text">LagentO réfléchit...</span>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <x-modal.confirm 
        show="showDeleteModal"
        title="Supprimer la conversation"
        confirm-text="Supprimer"
        cancel-text="Annuler"
        on-confirm="deleteConversation()"
        on-cancel="showDeleteModal = false"
        :danger="true"
        loading="isDeleting"
        loading-text="Suppression...">
        Êtes-vous sûr de vouloir supprimer la conversation "<span x-text="conversationToDelete.title" style="font-weight: var(--font-weight-medium);"></span>" ? 
        Cette action est irréversible et supprimera tous les messages de cette conversation.
    </x-modal.confirm>

</div>

@push('scripts')
<style>
/* Hide scrollbar but keep functionality */
.scrollbar-hidden {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hidden::-webkit-scrollbar {
    display: none;
}

/* Smooth scroll for conversation tabs */
#conversationTabs {
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}

/* Markdown prose styling */
.prose {
    max-width: none;
    color: var(--gray-700);
    line-height: 1.6;
}

.prose h1, .prose h2, .prose h3 {
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    line-height: 1.3;
}

.prose h1 {
    font-size: 1.5rem;
    font-weight: 700;
}

.prose h2 {
    font-size: 1.25rem;
    font-weight: 600;
}

.prose h3 {
    font-size: 1.125rem;
    font-weight: 600;
}

.prose ul, .prose ol {
    margin: 1rem 0;
    padding-left: 1.5rem;
}

.prose li {
    margin: 0.25rem 0;
}

.prose hr {
    margin: 2rem 0;
    border: 0;
    border-top: 1px solid var(--gray-200);
}

.prose p {
    margin: 1rem 0;
}

.prose strong {
    font-weight: 600;
    color: var(--gray-900);
}

.prose em {
    font-style: italic;
}

/* Animation du bouton copier */
.copy-button {
    overflow: visible;
    position: relative;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.copy-button i {
    transition: all 0.2s ease-in-out;
}

.copy-button.copy-success {
    animation: copyPulse 0.4s ease-out;
    background: var(--green-100) !important;
    border-color: var(--green-300) !important;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

.copy-button.copy-error {
    animation: shake 0.3s ease-in-out;
    background: var(--red-50) !important;
}

@keyframes copyPulse {
    0% {
        transform: scale(1);
    }
    30% {
        transform: scale(1.15);
    }
    60% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

@keyframes iconSuccess {
    0% {
        transform: scale(1) rotate(0deg);
        opacity: 1;
    }
    50% {
        transform: scale(1.3) rotate(10deg);
        opacity: 0.8;
    }
    100% {
        transform: scale(1.1) rotate(0deg);
        opacity: 1;
    }
}

@keyframes shake {
    0%, 100% {
        transform: translateX(0);
    }
    25% {
        transform: translateX(-4px);
    }
    75% {
        transform: translateX(4px);
    }
}

.copy-feedback {
    font-size: 0.75rem;
    z-index: 50;
    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

/* Messages utilisateur - texte toujours en blanc */
.user-message {
    background: var(--orange-primary) !important;
    color: white !important;
}

.user-message * {
    color: white !important;
}

.user-message p {
    color: white !important;
}

.user-message div {
    color: rgba(255, 255, 255, 0.8) !important;
}

</style>

<script>
function chatInterface() {
    return {
        isTyping: false,
        conversations: @json($conversations ?? []),
        messages: @json($messages ?? []),
        currentConversationId: '{{ $conversation->id ?? '' }}',
        showDeleteModal: false,
        isDeleting: false,
        conversationToDelete: { id: null, title: '' },
        
        init() {
            // Ensure messages have proper format and content field
            this.messages = this.messages.map(msg => ({
                ...msg,
                content: msg.content || msg.text_content || '', // Fallback for content field
                created_at: msg.created_at || new Date().toISOString()
            }));
            
            console.log('Messages loaded on init:', this.messages.length);
            console.log('Current conversation ID:', this.currentConversationId);
            
            this.loadConversations();
            this.scrollToBottom();
            
            // Scroll automatique avec délai pour s'assurer que le contenu est rendu
            setTimeout(() => {
                this.scrollToBottom();
            }, 100);
            
            // Vérifier si on doit soumettre automatiquement un message
            if (sessionStorage.getItem('autoSubmitMessage') === 'true') {
                sessionStorage.removeItem('autoSubmitMessage');
                const pendingMessage = sessionStorage.getItem('pendingMessage');
                
                if (pendingMessage) {
                    sessionStorage.removeItem('pendingMessage');
                    
                    // Vérifier si le message n'est pas déjà dans la conversation
                    const isDuplicate = this.messages.some(msg => 
                        msg.role === 'user' && 
                        msg.content.trim() === pendingMessage.trim()
                    );
                    
                    if (!isDuplicate) {
                        // Soumettre automatiquement le message après un court délai
                        setTimeout(() => {
                            this.sendDirectMessage(pendingMessage);
                        }, 100);
                    } else {
                        console.log('Message déjà présent, éviter la duplication');
                    }
                }
                
                // Vérifier si un fichier était attaché (pour informer l'utilisateur)
                if (sessionStorage.getItem('pendingFileAlert') === 'true') {
                    sessionStorage.removeItem('pendingFileAlert');
                    // On pourrait ajouter une notification ici si nécessaire
                }
                
                // Ne pas afficher les suggestions car un message va être soumis
                return;
            }
            // Vérifier si une nouvelle conversation vient d'être créée
            else if (sessionStorage.getItem('newConversationCreated') === 'true') {
                sessionStorage.removeItem('newConversationCreated');
                // Activer les suggestions après un court délai
                setTimeout(() => {
                    this.showSuggestionsAutomatically();
                }, 500);
            }
            // Activer automatiquement les suggestions si la conversation est vide
            else if (this.messages.length === 0) {
                setTimeout(() => {
                    this.showSuggestionsAutomatically();
                }, 1000);
            }
            
            // Initialiser les icônes Lucide
            this.$nextTick(() => {
                if (typeof window.renderIcons === 'function') {
                    window.renderIcons();
                }
            });
            
            // Refresh conversations periodically
            setInterval(() => {
                this.loadConversations();
            }, 30000);
        },
        
        async loadConversations() {
            try {
                const response = await fetch('/chat/conversations');
                const data = await response.json();
                if (data.success) {
                    this.conversations = data.conversations;
                    this.$nextTick(() => {
                        // Réinitialiser les icônes Lucide
                        if (typeof window.renderIcons === 'function') {
                            window.renderIcons();
                        }
                    });
                }
            } catch (error) {
                console.error('Erreur lors du chargement des conversations:', error);
            }
        },
        
        async createNewConversation() {
            try {
                const response = await fetch('/chat/conversations/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    // Stocker l'information qu'on vient de créer une nouvelle conversation
                    sessionStorage.setItem('newConversationCreated', 'true');
                    window.location.href = data.redirect_url;
                }
            } catch (error) {
                console.error('Erreur lors de la création de la conversation:', error);
            }
        },
        
        async switchToConversation(conversationId) {
            if (conversationId !== this.currentConversationId) {
                // Option 1: Navigation classique (garantit le rechargement des messages)
                window.location.href = `/chat?conversation=${conversationId}`;
                
                // Option 2: Navigation AJAX (plus rapide mais nécessite de recharger les messages)
                // await this.loadMessagesForConversation(conversationId);
                // this.currentConversationId = conversationId;
                // this.scrollToBottom();
            }
        },
        
        async loadMessagesForConversation(conversationId) {
            try {
                const response = await fetch(`/chat/conversations/${conversationId}/messages`);
                const data = await response.json();
                
                if (data.success) {
                    this.messages = data.messages.map(msg => ({
                        ...msg,
                        content: msg.content || msg.text_content || '',
                        created_at: msg.created_at || new Date().toISOString()
                    }));
                    
                    console.log('Messages loaded for conversation:', conversationId, this.messages.length);
                }
            } catch (error) {
                console.error('Erreur lors du chargement des messages:', error);
            }
        },
        
        confirmDeleteConversation(conversationId, conversationTitle) {
            this.conversationToDelete = {
                id: conversationId,
                title: conversationTitle
            };
            this.showDeleteModal = true;
        },
        
        async deleteConversation() {
            if (!this.conversationToDelete.id) return;
            
            this.isDeleting = true;
            
            try {
                const response = await fetch(`/chat/conversations/${this.conversationToDelete.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Supprimer de la liste locale
                    this.conversations = this.conversations.filter(c => c.id !== this.conversationToDelete.id);
                    
                    // Si c'était la conversation active, rediriger vers une autre ou créer une nouvelle
                    if (this.conversationToDelete.id === this.currentConversationId) {
                        if (this.conversations.length > 0) {
                            // Rediriger vers la première conversation restante
                            window.location.href = `/chat?conversation=${this.conversations[0].id}`;
                        } else {
                            // Créer une nouvelle conversation
                            await this.createNewConversation();
                        }
                    }
                    
                    this.showDeleteModal = false;
                    this.conversationToDelete = { id: null, title: '' };
                } else {
                    if (typeof window.showErrorToast === 'function') {
                        window.showErrorToast('Erreur lors de la suppression de la conversation');
                    }
                }
            } catch (error) {
                console.error('Erreur lors de la suppression:', error);
                if (typeof window.showErrorToast === 'function') {
                    window.showErrorToast('Erreur lors de la suppression de la conversation');
                }
            } finally {
                this.isDeleting = false;
            }
        },
        
        async sendQuickMessage(text) {
            // Utiliser directement la méthode sendDirectMessage pour éviter les doublons
            await this.sendDirectMessage(text);
        },
        
        async sendDirectMessage(message, attachment = null) {
            if (!message.trim()) return;
            
            try {
                // Récupérer le fichier attaché depuis le chat fixe si pas fourni
                if (!attachment) {
                    const fixedChatComponent = document.querySelector('[x-data*="fixedChat"]');
                    if (fixedChatComponent && fixedChatComponent._x_dataStack) {
                        attachment = fixedChatComponent._x_dataStack[0].attachedFile;
                    }
                }
                
                console.log('sendDirectMessage called with:', {
                    message: message,
                    hasAttachment: !!attachment,
                    attachmentName: attachment?.name
                });
                
                // Étape 1: Sauver le message utilisateur avec le fichier
                const formData = new FormData();
                formData.append('message', message.trim() || '');
                formData.append('conversation_id', this.currentConversationId);
                if (attachment) {
                    formData.append('file', attachment);
                }
                
                const saveResponse = await fetch('/chat/save-user-message', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });
                
                const saveData = await saveResponse.json();
                
                if (saveData.success) {
                    console.log('User message saved successfully:', saveData);
                    
                    // Ajouter le message utilisateur immédiatement avec l'attachement
                    const userMessage = {
                        id: saveData.user_message_id,
                        role: 'user',
                        content: message.trim() || '',
                        created_at: new Date().toISOString(),
                        attachment: saveData.attachment || null
                    };
                    
                    console.log('Adding user message with attachment:', userMessage);
                    this.messages.push(userMessage);
                    
                    // Nettoyer le fichier attaché du chat fixe
                    const fixedChatComponent = document.querySelector('[x-data*="fixedChat"]');
                    if (fixedChatComponent && fixedChatComponent._x_dataStack) {
                        const fixedData = fixedChatComponent._x_dataStack[0];
                        fixedData.attachedFile = null;
                        if (fixedData.$refs.fileInput) {
                            fixedData.$refs.fileInput.value = '';
                        }
                    }
                    
                    this.scrollToBottom();
                    
                    // Mettre à jour l'ID de conversation si nécessaire
                    if (saveData.conversation_id !== this.currentConversationId) {
                        this.currentConversationId = saveData.conversation_id;
                        const mainContainer = document.querySelector('[data-conversation-id]');
                        if (mainContainer) {
                            mainContainer.setAttribute('data-conversation-id', saveData.conversation_id);
                        }
                    }
                    
                    // Activer le typing
                    this.isTyping = true;
                    this.$nextTick(() => {
                        if (typeof window.renderIcons === 'function') {
                            window.renderIcons();
                        }
                    });
                    
                    // Étape 2: Traiter la réponse de l'agent avec streaming
                    console.log('Checking streaming availability:', typeof this.streamAIResponse);
                    if (typeof this.streamAIResponse === 'function') {
                        console.log('Using streaming response');
                        await this.streamAIResponse(message.trim(), saveData.vector_memory_id);
                    } else {
                        console.log('Streaming not available, fallback to regular response');
                        // Fallback vers la méthode classique si streaming pas disponible
                        const aiFormData = new FormData();
                        aiFormData.append('message', message.trim() || '');
                        aiFormData.append('conversation_id', this.currentConversationId);
                        if (saveData.vector_memory_id) {
                            aiFormData.append('vector_memory_id', saveData.vector_memory_id);
                        }
                        
                        const response = await fetch('/chat/send', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: aiFormData
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            console.log('AI response received:', data);
                            
                            // Ajouter la réponse AI
                            const aiMessage = {
                                id: data.message_id,
                                role: 'assistant',
                                content: data.response,
                                created_at: new Date().toISOString()
                            };
                            
                            this.messages.push(aiMessage);
                            await this.loadConversations();
                        } else {
                            console.error('Erreur API:', data.error);
                        }
                    }
                } else {
                    console.error('Erreur sauvegarde:', saveData.error);
                }
                
            } catch (error) {
                console.error('Erreur lors de l\'envoi:', error);
            } finally {
                this.isTyping = false;
                this.scrollToBottom();
                
                this.$nextTick(() => {
                    if (typeof window.renderIcons === 'function') {
                        window.renderIcons();
                    }
                });
            }
        },
        
        copyMessage(content, event) {
            navigator.clipboard.writeText(content).then(() => {
                // Show temporary feedback with animation
                const button = event.target.closest('button');
                const icon = button.querySelector('i');
                const feedback = button.querySelector('.copy-feedback');
                const originalLucide = icon.getAttribute('data-lucide');
                
                // Add success animation with enhanced visual feedback
                button.classList.add('copy-success');
                icon.setAttribute('data-lucide', 'check');
                icon.style.color = 'var(--green-600)';
                icon.style.animation = 'iconSuccess 0.6s ease-out';
                button.style.backgroundColor = 'var(--green-100)';
                button.style.borderColor = 'var(--green-300)';
                
                // Show feedback tooltip
                feedback.style.opacity = '1';
                feedback.style.transform = 'translateX(-50%) translateY(-4px)';
                
                if (typeof window.renderIcons === 'function') {
                    window.renderIcons();
                }
                
                // Reset after animation
                setTimeout(() => {
                    button.classList.remove('copy-success');
                    icon.setAttribute('data-lucide', originalLucide);
                    icon.style.color = 'var(--gray-500)';
                    icon.style.animation = '';
                    button.style.backgroundColor = '';
                    button.style.borderColor = '';
                    feedback.style.opacity = '0';
                    feedback.style.transform = 'translateX(-50%) translateY(0)';
                    
                    if (typeof window.renderIcons === 'function') {
                        window.renderIcons();
                    }
                }, 1500);
            }).catch(err => {
                console.error('Erreur lors de la copie:', err);
                // Show error feedback
                const button = event.target.closest('button');
                button.classList.add('copy-error');
                setTimeout(() => {
                    button.classList.remove('copy-error');
                }, 500);
            });
        },
        
        formatTime(dateString) {
            if (!dateString) return '';
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return '';
                return date.toLocaleTimeString('fr-FR', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } catch (error) {
                console.error('Erreur de formatage de la date:', error);
                return '';
            }
        },
        
        formatFileSize(size) {
            if (!size) return '';
            if (size < 1024) return size + ' B';
            if (size < 1024 * 1024) return (size / 1024).toFixed(1) + ' KB';
            if (size < 1024 * 1024 * 1024) return (size / (1024 * 1024)).toFixed(1) + ' MB';
            return (size / (1024 * 1024 * 1024)).toFixed(1) + ' GB';
        },
        
        scrollToBottom() {
            this.$nextTick(() => {
                const messagesArea = this.$refs.messagesArea;
                if (messagesArea) {
                    messagesArea.scrollTop = messagesArea.scrollHeight;
                }
            });
        },
        
        showSuggestionsAutomatically() {
            // Trouver le composant de suggestions et l'activer
            const suggestionsComponent = document.querySelector('[x-data*="suggestions"]');
            if (suggestionsComponent && suggestionsComponent._x_dataStack) {
                const suggestionsData = suggestionsComponent._x_dataStack[0];
                suggestionsData.showSuggestions = true;
                
                // Charger les suggestions si pas encore chargées
                if (suggestionsData.suggestionsList.length === 0 && !suggestionsData.isLoading) {
                    suggestionsData.loadSuggestions();
                }
            }
        },
        
        processMarkdown(content) {
            if (!content) return '';
            
            let html = content
                // Custom components - Cartes avec bouton détail (alignement horizontal coordonnées + bouton)
                .replace(/\[carte-institution:(.*?)\|(.*?)\|(.*?)\|(.*?)\]/g, '<div class="card my-2"><div class="card-header"><h4 class="card-title flex items-center gap-2"><i data-lucide="building" class="w-4 h-4"></i>$1</h4></div><div class="card-body"><p class="text-sm text-gray-600 mb-2">$2</p><div class="flex items-center justify-between"><div class="flex items-center gap-1 text-xs text-gray-500"><i data-lucide="phone" class="w-3 h-3"></i>$3</div><a href="$4" target="_blank" class="inline-flex items-center gap-1 px-4 py-2 text-xs font-medium rounded-md transition-colors" style="border: 1px solid var(--orange-primary); color: var(--orange-primary);" onmouseover="this.style.backgroundColor=\'var(--orange-primary)\'; this.style.color=\'white\'" onmouseout="this.style.backgroundColor=\'transparent\'; this.style.color=\'var(--orange-primary)\'">Détails</a></div></div></div>')
                .replace(/\[carte-opportunite:(.*?)\|(.*?)\|(.*?)\|(.*?)\]/g, '<div class="card my-2"><div class="card-header"><h4 class="card-title flex items-center gap-2"><i data-lucide="target" class="w-4 h-4"></i>$1</h4></div><div class="card-body"><p class="text-sm text-gray-600 mb-2">$2</p><div class="flex items-center justify-between"><p class="text-xs font-medium" style="color: var(--orange-primary);">$3</p><a href="$4" target="_blank" class="inline-flex items-center gap-1 px-4 py-2 text-xs font-medium rounded-md transition-colors" style="border: 1px solid var(--orange-primary); color: var(--orange-primary);" onmouseover="this.style.backgroundColor=\'var(--orange-primary)\'; this.style.color=\'white\'" onmouseout="this.style.backgroundColor=\'transparent\'; this.style.color=\'var(--orange-primary)\'">Détails</a></div></div></div>')
                .replace(/\[carte-texte-officiel:(.*?)\|(.*?)\|(.*?)\|(.*?)\]/g, '<div class="card my-2"><div class="card-header"><h4 class="card-title flex items-center gap-2"><i data-lucide="file-text" class="w-4 h-4"></i>$1</h4></div><div class="card-body"><p class="text-sm text-gray-600 mb-2">$2</p><div class="flex items-center justify-between"><p class="text-xs text-blue-600 font-medium">$3</p><a href="$4" target="_blank" class="inline-flex items-center gap-1 px-4 py-2 text-xs font-medium rounded-md transition-colors" style="border: 1px solid var(--orange-primary); color: var(--orange-primary);" onmouseover="this.style.backgroundColor=\'var(--orange-primary)\'; this.style.color=\'white\'" onmouseout="this.style.backgroundColor=\'transparent\'; this.style.color=\'var(--orange-primary)\'">Détails</a></div></div></div>')
                .replace(/\[carte-partenaire:(.*?)\|(.*?)\|(.*?)\|(.*?)\]/g, '<div class="card my-2"><div class="card-header"><h4 class="card-title flex items-center gap-2"><i data-lucide="users" class="w-4 h-4"></i>$1</h4></div><div class="card-body"><p class="text-sm text-gray-600 mb-2">$2</p><div class="flex items-center justify-between"><p class="text-xs text-green-600 font-medium">$3</p><a href="$4" target="_blank" class="inline-flex items-center gap-1 px-4 py-2 text-xs font-medium rounded-md transition-colors" style="border: 1px solid var(--orange-primary); color: var(--orange-primary);" onmouseover="this.style.backgroundColor=\'var(--orange-primary)\'; this.style.color=\'white\'" onmouseout="this.style.backgroundColor=\'transparent\'; this.style.color=\'var(--orange-primary)\'">Détails</a></div></div></div>')
                
                // Headers (espacement ultra-réduit)
                .replace(/^### (.*$)/gm, '<h3 class="text-lg font-semibold mt-2 mb-1" style="color: var(--gray-900);">$1</h3>')
                .replace(/^## (.*$)/gm, '<h2 class="text-xl font-bold mt-3 mb-1" style="color: var(--gray-900);">$1</h2>')
                .replace(/^# (.*$)/gm, '<h1 class="text-2xl font-bold mt-3 mb-2" style="color: var(--gray-900);">$1</h1>')
                
                // Bold and italic
                .replace(/\*\*(.*?)\*\*/g, '<strong class="font-semibold" style="color: var(--gray-900);">$1</strong>')
                .replace(/\*(.*?)\*/g, '<em class="italic">$1</em>')
                
                // Links with explicit target="_blank" format
                .replace(/\[([^\]]+)\]\(([^)]+)\)\{target="_blank"\}/g, '<a href="$2" target="_blank" class="text-orange-600 hover:text-orange-700 underline">$1</a>')
                // Regular links (also with target blank by default)
                .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" class="text-orange-600 hover:text-orange-700 underline">$1</a>');
            
            // Process lists with proper numbering and compact spacing
            html = this.processLists(html);
            
            // Horizontal rules
            html = html.replace(/^---$/gm, '<hr class="my-4 border-gray-200">');
            
            // Process paragraphs with compact spacing
            html = this.processParagraphs(html);
            
            // Trigger icon rendering after content update
            setTimeout(() => {
                if (typeof window.renderIcons === 'function') {
                    window.renderIcons();
                }
            }, 100);
            
            return html;
        },
        
        processLists(html) {
            // Split into lines for processing
            let lines = html.split('\n');
            let result = [];
            let currentList = null;
            let listItems = [];
            
            for (let i = 0; i < lines.length; i++) {
                let line = lines[i];
                
                // Check for unordered list items
                if (line.match(/^[-*+]\s+(.+)/)) {
                    let content = line.replace(/^[-*+]\s+/, '');
                    if (currentList !== 'ul') {
                        if (currentList) {
                            result.push(this.closePreviousList(currentList, listItems));
                            listItems = [];
                        }
                        currentList = 'ul';
                    }
                    listItems.push(`<li class="mb-0">${content}</li>`);
                }
                // Check for ordered list items
                else if (line.match(/^\d+\.\s+(.+)/)) {
                    let content = line.replace(/^\d+\.\s+/, '');
                    if (currentList !== 'ol') {
                        if (currentList) {
                            result.push(this.closePreviousList(currentList, listItems));
                            listItems = [];
                        }
                        currentList = 'ol';
                    }
                    listItems.push(`<li class="mb-0">${content}</li>`);
                }
                // Not a list item
                else {
                    if (currentList) {
                        result.push(this.closePreviousList(currentList, listItems));
                        currentList = null;
                        listItems = [];
                    }
                    if (line.trim()) {
                        result.push(line);
                    }
                }
            }
            
            // Close any remaining list
            if (currentList) {
                result.push(this.closePreviousList(currentList, listItems));
            }
            
            return result.join('\n');
        },
        
        closePreviousList(listType, items) {
            if (listType === 'ul') {
                return `<ul class="list-disc pl-6 mb-2 space-y-0">${items.join('')}</ul>`;
            } else if (listType === 'ol') {
                return `<ol class="list-decimal pl-6 mb-2 space-y-0">${items.join('')}</ol>`;
            }
            return '';
        },
        
        processParagraphs(html) {
            // Split by double line breaks for paragraphs
            let paragraphs = html.split(/\n\s*\n/);
            
            return paragraphs.map(paragraph => {
                paragraph = paragraph.trim();
                if (!paragraph) return '';
                
                // Skip if already wrapped in block elements
                if (paragraph.match(/^<(h[1-6]|ul|ol|div|hr)/)) {
                    return paragraph;
                }
                
                // Replace single line breaks with <br> within paragraphs
                paragraph = paragraph.replace(/\n/g, '<br>');
                
                // Wrap in paragraph tag with ultra-compact spacing
                return `<p class="mb-1 leading-relaxed">${paragraph}</p>`;
            }).join('\n');
        }
    }
}

// Icons are already initialized by app.js, no need to do it here
</script>
@endpush

@push('scripts')
{{-- Streaming enhancements are now included at the top for proper loading order --}}
@endpush

@push('styles')
<style>
.thinking-text {
    color: #111827 !important; /* gray-900 for light mode */
}

[data-theme="dark"] .thinking-text {
    color: #f9fafb !important; /* gray-50 for dark mode */
}
</style>
@endpush

@endsection
