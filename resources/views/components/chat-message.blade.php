@props([
    'role' => 'user',
    'content' => '',
    'timestamp' => null,
    'status' => null
])

<div class="message {{ $role }}">
    @if($role === 'assistant')
        <div class="message-content">
            <div class="message-text">
                {!! Str::markdown($content) !!}
            </div>
            
            <div class="message-actions">
                <button 
                    class="action-btn"
                    @click="navigator.clipboard.writeText('{{ addslashes($content) }}')"
                    title="Copier"
                >
                    <i data-lucide="copy" class="w-4 h-4"></i>
                </button>
                
                <button 
                    class="action-btn"
                    title="Régénérer"
                >
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                </button>
            </div>
            
            @if($timestamp)
                <div class="text-xs text-gray-500 mt-2">
                    {{ $timestamp }}
                </div>
            @endif
        </div>
    @else
        <div class="user-message">
            {{ $content }}
            
            @if($status === 'sending')
                <div class="inline-block ml-2">
                    <div class="loading-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            @endif
        </div>
        
        @if($timestamp)
            <div class="text-xs text-gray-500 mt-1 text-right">
                {{ $timestamp }}
            </div>
        @endif
    @endif
</div>