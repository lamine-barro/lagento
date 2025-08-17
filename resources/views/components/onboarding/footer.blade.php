@php
    $prevUrl = $prevUrl ?? url()->previous();
    $nextLabel = $nextLabel ?? 'Suivant';
    $nextFormId = $nextFormId ?? null; // if provided, submit that form
    $isFinal = $isFinal ?? false;
@endphp

<div class="flex justify-between items-center mt-12 pt-6">
    <div class="w-full max-w-4xl mx-auto flex justify-between items-center gap-4 mt-4">
    <a href="{{ $prevUrl }}" class="btn btn-ghost">
        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
        Retour
    </a>

    @if ($nextFormId)
        <button type="submit" form="{{ $nextFormId }}" class="btn btn-primary">
            {{ $isFinal ? 'Finaliser' : $nextLabel }}
            <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
        </button>
    @else
        <button class="btn btn-primary" onclick="document.querySelector('form')?.submit()">
            {{ $isFinal ? 'Finaliser' : $nextLabel }}
            <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
        </button>
    @endif
    </div>
</div>


