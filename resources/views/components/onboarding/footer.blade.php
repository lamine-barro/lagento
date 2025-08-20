@php
    $prevUrl = $prevUrl ?? url()->previous();
    $nextLabel = $nextLabel ?? 'Suivant';
    $nextFormId = $nextFormId ?? null; // if provided, submit that form
    $isFinal = $isFinal ?? false;
@endphp

<div class="flex justify-between items-center mt-12 pt-6" x-data="{ isSubmitting: false }">
    <div class="w-full max-w-4xl mx-auto flex justify-between items-center gap-4 mt-4">
    <a href="{{ $prevUrl }}" class="btn btn-ghost" x-bind:disabled="isSubmitting">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
        Retour
    </a>

    @if ($nextFormId)
        <button type="submit" form="{{ $nextFormId }}" class="btn btn-primary" 
                x-bind:disabled="isSubmitting"
                @click="isSubmitting = true"
                x-bind:class="{ 'opacity-75 cursor-not-allowed': isSubmitting }">
            <template x-if="!isSubmitting">
                <span class="flex items-center">
                    {{ $isFinal ? 'Finaliser' : $nextLabel }}
                    <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                </span>
            </template>
            <template x-if="isSubmitting">
                <span class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ $isFinal ? 'Finalisation...' : 'Traitement...' }}
                </span>
            </template>
        </button>
    @else
        <button class="btn btn-primary" 
                x-bind:disabled="isSubmitting"
                @click="isSubmitting = true; document.querySelector('form')?.submit()"
                x-bind:class="{ 'opacity-75 cursor-not-allowed': isSubmitting }">
            <template x-if="!isSubmitting">
                <span class="flex items-center">
                    {{ $isFinal ? 'Finaliser' : $nextLabel }}
                    <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                </span>
            </template>
            <template x-if="isSubmitting">
                <span class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ $isFinal ? 'Finalisation...' : 'Traitement...' }}
                </span>
            </template>
        </button>
    @endif
    </div>
</div>


