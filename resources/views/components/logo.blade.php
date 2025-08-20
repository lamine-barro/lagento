@php
    $size = $size ?? 'md';
    $classes = $class ?? '';
    
    $sizeClasses = [
        'sm' => 'h-6 w-auto',
        'md' => 'h-10 w-auto', 
        'lg' => 'h-14 w-auto',
        'xl' => 'h-20 w-auto',
        '2xl' => 'h-24 w-auto'
    ];
    
    $logoClass = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div class="flex items-center justify-center {{ $classes }}">
    <!-- Logo light (visible en mode light) -->
    <img src="/logo-light.png" 
         alt="Agento" 
         class="{{ $logoClass }} dark:hidden"
         style="display: var(--logo-light-display, block);">
    
    <!-- Logo dark (visible en mode dark) -->
    <img src="/logo-dark.png" 
         alt="Agento" 
         class="{{ $logoClass }} hidden dark:block"
         style="display: var(--logo-dark-display, none);">
</div>

<style>
[data-theme="dark"] {
    --logo-light-display: none;
    --logo-dark-display: block;
}

[data-theme="light"] {
    --logo-light-display: block;
    --logo-dark-display: none;
}
</style>