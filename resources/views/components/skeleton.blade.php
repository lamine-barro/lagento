@props([
    'type' => 'text',
    'lines' => 3,
    'height' => null,
    'width' => null,
    'rounded' => false
])

@php
    $classes = 'skeleton';
    
    $classes .= match($type) {
        'text' => ' skeleton-text',
        'title' => ' skeleton-title',
        'avatar' => ' skeleton-avatar',
        'button' => ' skeleton-button',
        'card' => ' skeleton-card',
        default => ''
    };
    
    if ($rounded) {
        $classes .= ' rounded-full';
    }
    
    $style = '';
    if ($height) {
        $style .= "height: {$height};";
    }
    if ($width) {
        $style .= "width: {$width};";
    }
@endphp

@if($type === 'text' && $lines > 1)
    @for($i = 0; $i < $lines; $i++)
        <div 
            class="{{ $classes }}"
            @if($i === $lines - 1 && $lines > 1) style="width: 80%;" @endif
        ></div>
    @endfor
@else
    <div 
        {{ $attributes->merge(['class' => $classes]) }}
        @if($style) style="{{ $style }}" @endif
    ></div>
@endif