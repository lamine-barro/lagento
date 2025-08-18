@props([
    'show' => false,
    'maxWidth' => 'md',
    'closable' => true,
    'zIndex' => '99999'
])

@php
    $maxWidthClass = match($maxWidth) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        'full' => 'max-w-full',
        default => 'max-w-md'
    };
@endphp

<template x-teleport="body">
    <div x-show="{{ $show }}" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 overflow-y-auto" 
         style="z-index: {{ $zIndex }} !important; display: none;">
        
        <!-- Blurred Backdrop -->
        <div class="fixed inset-0" 
             style="background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px);" 
             @if($closable) @click="{{ $show }} = false" @endif></div>
        
        <!-- Modal Container -->
        <div class="flex items-center justify-center min-h-screen px-4 py-4" style="position: relative; z-index: {{ $zIndex + 1 }};">
            <div x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="card {{ $maxWidthClass }} w-full mx-4"
                 style="position: relative; z-index: {{ $zIndex + 2 }};"
                 @click.stop>
                
                {{ $slot }}
                
            </div>
        </div>
    </div>
</template>