@props([
    'name' => 'modal',
    'title' => '',
    'maxWidth' => 'md',
    'footer' => null
])

@php
    $maxWidthClass = match($maxWidth) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        'full' => 'max-w-full',
        default => 'max-w-md'
    };
@endphp

<div 
    x-data="{ open: false }"
    x-on:open-{{ $name }}.window="open = true"
    x-on:close-{{ $name }}.window="open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    class="fixed inset-0 z-50"
    style="display: none;"
>
    <!-- Backdrop -->
    <div 
        x-show="open"
        x-transition:enter="fade-in"
        x-transition:leave="fade-out"
        class="modal-backdrop"
        @click="open = false"
    >
        <!-- Modal -->
        <div 
            x-show="open"
            x-transition:enter="scale-in"
            x-transition:leave="scale-out"
            @click.stop
            class="modal {{ $maxWidthClass }}"
        >
            @if($title)
                <div class="modal-header">
                    <h2 class="modal-title">{{ $title }}</h2>
                    <button 
                        @click="open = false"
                        class="p-2 hover:bg-gray-100 rounded-lg transition"
                    >
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            @endif
            
            <div class="modal-body">
                {{ $slot }}
            </div>
            
            @if($footer)
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>