@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left',
    'loading' => false,
    'fullWidth' => false
])

@php
    $classes = 'btn';
    
    // Variant classes
    $classes .= match($variant) {
        'primary' => ' btn-primary',
        'secondary' => ' btn-secondary',
        'ghost' => ' btn-ghost',
        default => ' btn-primary'
    };
    
    // Size classes
    $classes .= match($size) {
        'sm' => ' btn-sm',
        'lg' => ' btn-lg',
        default => ''
    };
    
    // Full width
    if ($fullWidth) {
        $classes .= ' w-full';
    }
    
    // Disabled state
    if ($disabled || $loading) {
        $classes .= ' state-disabled';
    }
@endphp

<button 
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $classes]) }}
    @disabled($disabled || $loading)
>
    @if($loading)
        <div class="spinner spinner-sm"></div>
    @else
        @if($icon && $iconPosition === 'left')
            <i data-lucide="{{ $icon }}"></i>
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right')
            <i data-lucide="{{ $icon }}"></i>
        @endif
    @endif
</button>