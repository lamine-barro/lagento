@props([
    'type' => 'info',
    'dismissible' => false,
    'icon' => null
])

@php
    $classes = 'alert';
    
    $classes .= match($type) {
        'success' => ' alert-success',
        'warning' => ' alert-warning',
        'danger' => ' alert-danger',
        'error' => ' alert-danger',
        default => ' alert-info'
    };
    
    $defaultIcon = match($type) {
        'success' => 'check-circle',
        'warning' => 'alert-triangle',
        'danger', 'error' => 'x-circle',
        default => 'info'
    };
    
    $icon = $icon ?? $defaultIcon;
@endphp

<div 
    {{ $attributes->merge(['class' => $classes]) }}
    @if($dismissible)
        x-data="{ show: true }"
        x-show="show"
        x-transition
    @endif
>
    <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
    
    <div class="flex-1">
        {{ $slot }}
    </div>
    
    @if($dismissible)
        <button 
            @click="show = false"
            class="ml-auto p-1 hover:bg-white/20 rounded transition"
        >
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    @endif
</div>