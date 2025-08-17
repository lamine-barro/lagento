@props([
    'title' => null,
    'subtitle' => null,
    'interactive' => false,
    'padding' => true,
    'footer' => null,
    'headerActions' => null
])

@php
    $classes = 'card';
    
    if ($interactive) {
        $classes .= ' card-interactive';
    }
    
    if (!$padding) {
        $classes = str_replace('card', 'card p-0', $classes);
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if($title || $headerActions)
        <div class="card-header flex justify-between items-center">
            <div>
                @if($title)
                    <h3 class="card-title">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-sm text-gray-500 mt-1">{{ $subtitle }}</p>
                @endif
            </div>
            @if($headerActions)
                <div class="flex gap-2">
                    {{ $headerActions }}
                </div>
            @endif
        </div>
    @endif
    
    <div class="card-body">
        {{ $slot }}
    </div>
    
    @if($footer)
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
</div>