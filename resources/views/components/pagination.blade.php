@if ($paginator->hasPages())
<nav class="flex items-center justify-between border-t pt-6" style="border-color: var(--gray-200);">
    <!-- Informations sur la pagination -->
    <div class="flex-1 flex justify-between sm:hidden">
        {{-- Mobile: boutons précédent/suivant uniquement --}}
        @if ($paginator->onFirstPage())
            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md cursor-not-allowed"
                  style="color: var(--gray-400); background: var(--gray-100);">
                Précédent
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" 
               class="relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors hover:bg-gray-100"
               style="color: var(--gray-700); background: var(--white); border: 1px solid var(--gray-300);">
                Précédent
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" 
               class="ml-3 relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors hover:bg-gray-100"
               style="color: var(--gray-700); background: var(--white); border: 1px solid var(--gray-300);">
                Suivant
            </a>
        @else
            <span class="ml-3 relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md cursor-not-allowed"
                  style="color: var(--gray-400); background: var(--gray-100);">
                Suivant
            </span>
        @endif
    </div>

    <!-- Desktop: pagination complète -->
    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
            <p class="text-sm" style="color: var(--gray-700);">
                Affichage de
                <span class="font-medium">{{ $paginator->firstItem() }}</span>
                à
                <span class="font-medium">{{ $paginator->lastItem() }}</span>
                sur
                <span class="font-medium">{{ $paginator->total() }}</span>
                résultats
            </p>
        </div>
        
        <div class="flex items-center gap-1">
            {{-- Bouton précédent --}}
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-2 py-2 rounded-l-md cursor-not-allowed"
                      style="color: var(--gray-400); background: var(--gray-100); border: 1px solid var(--gray-300);">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" 
                   class="relative inline-flex items-center px-2 py-2 rounded-l-md transition-colors hover:bg-gray-100"
                   style="color: var(--gray-700); background: var(--white); border: 1px solid var(--gray-300);">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </a>
            @endif

            {{-- Numéros de page --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium cursor-not-allowed"
                          style="color: var(--gray-400); background: var(--white); border: 1px solid var(--gray-300);">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium"
                                  style="color: var(--white); background: var(--orange); border: 1px solid var(--orange);">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" 
                               class="relative inline-flex items-center px-4 py-2 text-sm font-medium transition-colors hover:bg-gray-50"
                               style="color: var(--gray-700); background: var(--white); border: 1px solid var(--gray-300);">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Bouton suivant --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" 
                   class="relative inline-flex items-center px-2 py-2 rounded-r-md transition-colors hover:bg-gray-100"
                   style="color: var(--gray-700); background: var(--white); border: 1px solid var(--gray-300);">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            @else
                <span class="relative inline-flex items-center px-2 py-2 rounded-r-md cursor-not-allowed"
                      style="color: var(--gray-400); background: var(--gray-100); border: 1px solid var(--gray-300);">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </span>
            @endif
        </div>
    </div>
</nav>
@endif