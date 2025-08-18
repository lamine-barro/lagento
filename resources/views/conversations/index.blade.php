@extends('layouts.app')

@section('title', 'Historique des conversations')

@section('content')
<div class="min-h-screen bg-white">
    <!-- Header -->
    <div class="sticky top-0 z-10 bg-white border-b p-4" style="border-color: var(--gray-100);">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-medium" style="color: var(--gray-900);">Conversations</h1>
            <button 
                @click="$dispatch('open-new-conversation')"
                class="btn btn-primary btn-sm"
            >
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                Nouvelle conversation
            </button>
        </div>
    </div>

    <!-- Conversations List -->
    <div class="max-w-4xl mx-auto p-4 space-y-3">
        @forelse($conversations ?? [] as $conversation)
            <div class="border rounded-lg p-4 cursor-pointer hover:shadow-sm transition-shadow" 
                 style="border-color: var(--gray-100);"
                 onclick="window.location.href = '{{ route('chat.index', ['conversation' => $conversation->id]) }}'">
                
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-start gap-3 flex-1">
                        <!-- Pin Status -->
                        @if($conversation->is_pinned)
                            <i data-lucide="pin" class="w-4 h-4 mt-0.5" style="color: var(--orange-primary);"></i>
                        @endif
                        
                        <!-- Conversation Info -->
                        <div class="flex-1">
                            <h3 class="font-medium text-sm" style="color: var(--gray-900);">
                                {{ $conversation->title }}
                            </h3>
                            
                            @if($conversation->last_message)
                                <p class="text-xs mt-1 line-clamp-2" style="color: var(--gray-700);">
                                    {{ Str::limit($conversation->last_message, 100) }}
                                </p>
                            @endif
                            
                            <div class="flex items-center gap-4 mt-2 text-xs" style="color: var(--gray-500);">
                                <span>{{ $conversation->message_count }} messages</span>
                                <span>{{ $conversation->last_message_at?->diffForHumans() ?? $conversation->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button 
                            @click.stop="open = !open"
                            class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            <i data-lucide="more-vertical" class="w-4 h-4" style="color: var(--gray-500);"></i>
                        </button>
                        
                        <div x-show="open" 
                             @click.away="open = false"
                             class="absolute right-0 top-8 bg-white border rounded-lg shadow-lg min-w-32 z-20"
                             style="border-color: var(--gray-200);"
                             x-transition>
                            
                            <!-- Pin/Unpin -->
                            <button 
                                @click.stop="togglePin({{ $conversation->id }})"
                                class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"
                                style="color: var(--gray-700);"
                            >
                                <i data-lucide="{{ $conversation->is_pinned ? 'pin-off' : 'pin' }}" class="w-4 h-4"></i>
                                {{ $conversation->is_pinned ? 'Désépingler' : 'Épingler' }}
                            </button>
                            
                            <!-- Rename -->
                            <button 
                                @click.stop="renameConversation({{ $conversation->id }}, '{{ $conversation->title }}')"
                                class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"
                                style="color: var(--gray-700);"
                            >
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                Renommer
                            </button>
                            
                            <!-- Delete -->
                            <button 
                                @click.stop="deleteConversation({{ $conversation->id }})"
                                class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"
                                style="color: var(--danger);"
                            >
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <i data-lucide="message-square" class="w-12 h-12 mx-auto mb-4" style="color: var(--gray-300);"></i>
                <h3 class="text-lg font-medium mb-2" style="color: var(--gray-900);">Aucune conversation</h3>
                <p class="text-sm mb-6" style="color: var(--gray-500);">
                    Commencez votre première conversation avec Agent O
                </p>
                <button 
                    @click="$dispatch('open-new-conversation')"
                    class="btn btn-primary"
                >
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    Nouvelle conversation
                </button>
            </div>
        @endforelse
    </div>
</div>

<!-- New Conversation Modal -->
<div x-data="{ open: false }" 
     @open-new-conversation.window="open = true"
     x-show="open" 
     class="fixed inset-0 z-50" 
     style="display: none;">
    
    <div class="modal-backdrop" @click="open = false">
        <div @click.stop class="modal max-w-md">
            <div class="p-6">
                <h3 class="text-lg font-medium mb-4" style="color: var(--gray-900);">
                    Nouvelle conversation
                </h3>
                
                <form @submit.prevent="createConversation()">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2" style="color: var(--gray-700);">
                            Titre de la conversation
                        </label>
                        <input 
                            type="text"
                            x-model="newConversationTitle"
                            placeholder="Ex: Stratégie de financement"
                            class="input-field w-full"
                            required
                        />
                    </div>
                    
                    <div class="flex gap-3">
                        <button 
                            type="button"
                            @click="open = false"
                            class="btn btn-ghost flex-1"
                        >
                            Annuler
                        </button>
                        <button 
                            type="submit"
                            class="btn btn-primary flex-1"
                        >
                            Créer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePin(conversationId) {
    fetch(`/conversations/${conversationId}/pin`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).then(() => {
        location.reload();
    });
}

function renameConversation(conversationId, currentTitle) {
    const newTitle = prompt('Nouveau titre:', currentTitle);
    if (newTitle && newTitle !== currentTitle) {
        fetch(`/conversations/${conversationId}/rename`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ title: newTitle })
        }).then(() => {
            location.reload();
        });
    }
}

function deleteConversation(conversationId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette conversation ?')) {
        fetch(`/conversations/${conversationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => {
            location.reload();
        });
    }
}

function createConversation() {
    const title = document.querySelector('[x-model="newConversationTitle"]').value;
    
    fetch('/conversations', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ title })
    })
    .then(response => response.json())
    .then(data => {
        window.location.href = `/chat?conversation=${data.id}`;
    });
}
</script>
@endpush
@endsection