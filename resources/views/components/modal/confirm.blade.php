@props([
    'show' => 'false',
    'title' => 'Confirmation',
    'message' => '',
    'confirmText' => 'Confirmer',
    'cancelText' => 'Annuler',
    'onConfirm' => '',
    'onCancel' => '',
    'icon' => 'alert-triangle',
    'iconColor' => 'var(--warning)',
    'iconBg' => 'var(--warning-100)',
    'danger' => false,
    'loading' => 'false',
    'loadingText' => 'Traitement...',
    'zIndex' => '99999'
])

@php
    if ($danger) {
        $iconColor = 'var(--danger)';
        $iconBg = 'var(--danger-100)';
        $icon = $icon === 'alert-triangle' ? 'trash-2' : $icon;
    }
@endphp

<x-modal.base :show="$show" max-width="md" :z-index="$zIndex">
    <!-- Modal Header -->
    <div class="card-header">
        <div class="flex items-center" style="gap: var(--space-3);">
            <div class="flex items-center justify-center flex-shrink-0" 
                 style="width: 40px; height: 40px; border-radius: var(--radius-full); background: {{ $iconBg }};">
                <i data-lucide="{{ $icon }}" class="w-5 h-5" style="color: {{ $iconColor }};"></i>
            </div>
            <h3 class="card-title">{{ $title }}</h3>
        </div>
    </div>
    
    <!-- Modal Body -->
    <div class="card-body">
        <p style="color: var(--gray-600); margin: 0;">
            {{ $message }}
            {{ $slot }}
        </p>
    </div>
    
    <!-- Modal Footer -->
    <div class="card-footer">
        <div class="flex justify-end" style="gap: var(--space-3);">
            <button type="button" 
                    @if($onCancel) @click="{{ $onCancel }}" @else @click="{{ $show }} = false" @endif
                    class="btn btn-secondary">
                {{ $cancelText }}
            </button>
            <button type="button" 
                    @if($onConfirm) @click="{{ $onConfirm }}" @endif
                    :disabled="{{ $loading }}"
                    class="btn @if($danger) btn-danger @else btn-primary @endif">
                <span x-show="!{{ $loading }}">{{ $confirmText }}</span>
                <span x-show="{{ $loading }}" class="flex items-center" style="gap: var(--space-2);">
                    <i data-lucide="loader-2" class="w-4 h-4 smooth-spin"></i>
                    <span class="shimmer-text">{{ $loadingText }}</span>
                </span>
            </button>
        </div>
    </div>
</x-modal.base>