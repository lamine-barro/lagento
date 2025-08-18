@props([
    'show' => 'false',
    'title' => '',
    'maxWidth' => 'md',
    'closable' => true,
    'showHeader' => true,
    'showFooter' => false,
    'onClose' => '',
    'zIndex' => '99999'
])

<x-modal.base :show="$show" :max-width="$maxWidth" :closable="$closable" :z-index="$zIndex">
    @if($showHeader && $title)
        <!-- Modal Header -->
        <div class="card-header">
            <div class="flex items-center justify-between">
                <h3 class="card-title">{{ $title }}</h3>
                @if($closable)
                    <button type="button" 
                            @if($onClose) @click="{{ $onClose }}" @else @click="{{ $show }} = false" @endif
                            class="p-2 rounded-lg transition-colors"
                            style="color: var(--gray-500);"
                            onmouseover="this.style.background='var(--gray-100)'"
                            onmouseout="this.style.background='transparent'">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                @endif
            </div>
        </div>
    @endif
    
    <!-- Modal Body -->
    <div class="card-body">
        {{ $slot }}
    </div>
    
    @if($showFooter)
        <!-- Modal Footer -->
        <div class="card-footer">
            {{ $footer ?? '' }}
        </div>
    @endif
</x-modal.base>