<!-- Grille de projets -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6 mb-8">
    @forelse($projets as $projet)
        <div class="project-card rounded-xl shadow-sm p-4 md:p-6">
            <!-- En-tête du projet -->
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1 line-clamp-2">
                        {{ $projet->nom }}
                    </h3>
                    <p class="text-sm text-gray-600 mb-2">
                        Par {{ $projet->user->name ?? 'Entrepreneur' }}
                    </p>
                </div>
                <div class="ml-2">
                    @if($projet->formalise === 'oui')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Formalisé
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            En cours
                        </span>
                    @endif
                </div>
            </div>

            <!-- Description -->
            <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                {{ $projet->description ?? 'Aucune description disponible' }}
            </p>

            <!-- Secteurs -->
            @if($projet->secteurs && count($projet->secteurs) > 0)
                <div class="flex flex-wrap gap-1 mb-4">
                    @foreach(array_slice($projet->secteurs, 0, 2) as $secteur)
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $secteur }}
                        </span>
                    @endforeach
                    @if(count($projet->secteurs) > 2)
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-500">
                            +{{ count($projet->secteurs) - 2 }}
                        </span>
                    @endif
                </div>
            @endif

            <!-- Informations supplémentaires -->
            <div class="space-y-2 mb-4">
                @if($projet->region)
                    <div class="flex items-center text-xs text-gray-500">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $projet->region }}
                    </div>
                @endif

                @if($projet->maturite)
                    <div class="flex items-center text-xs text-gray-500">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        {{ $projet->maturite }}
                    </div>
                @endif

                @if($projet->stade_financement)
                    <div class="flex items-center text-xs text-gray-500">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                        {{ $projet->stade_financement }}
                    </div>
                @endif
            </div>

            <!-- Pied de carte -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <span class="text-xs text-gray-500">
                    {{ $projet->created_at ? $projet->created_at->diffForHumans() : 'Date inconnue' }}
                </span>
                
                <button class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg transition-all duration-200 border"
                        style="color: var(--orange-primary); border-color: var(--orange-primary);"
                        onclick="viewProject({{ $projet->id }})"
                        onmouseover="this.style.backgroundColor='var(--orange-primary)'; this.style.color='white';"
                        onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--orange-primary)';">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Voir
                </button>
            </div>
        </div>
    @empty
        <div class="col-span-full">
            <div class="text-center py-12">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun projet trouvé</h3>
                <p class="text-gray-600">Aucun projet ne correspond à vos critères de recherche.</p>
            </div>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($projets->hasPages())
    <div class="projects-card rounded-xl shadow-sm p-4 md:p-6">
        <div class="flex items-center justify-between">
            <!-- Informations -->
            <div class="flex-1 flex justify-between sm:hidden">
                @if ($projets->onFirstPage())
                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                        Précédent
                    </span>
                @else
                    <button onclick="changePage({{ $projets->currentPage() - 1 }})" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-orange-300 focus:border-orange-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                        Précédent
                    </button>
                @endif

                @if ($projets->hasMorePages())
                    <button onclick="changePage({{ $projets->currentPage() + 1 }})" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-orange-300 focus:border-orange-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                        Suivant
                    </button>
                @else
                    <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                        Suivant
                    </span>
                @endif
            </div>

            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Affichage de
                        <span class="font-medium">{{ $projets->firstItem() }}</span>
                        à
                        <span class="font-medium">{{ $projets->lastItem() }}</span>
                        sur
                        <span class="font-medium">{{ $projets->total() }}</span>
                        projet{{ $projets->total() > 1 ? 's' : '' }}
                    </p>
                </div>

                <div>
                    <span class="relative z-0 inline-flex shadow-sm rounded-md">
                        {{-- Previous Page Link --}}
                        @if ($projets->onFirstPage())
                            <span aria-disabled="true" aria-label="Précédent">
                                <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md leading-5" aria-hidden="true">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </span>
                        @else
                            <button onclick="changePage({{ $projets->currentPage() - 1 }})" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-orange-300 focus:border-orange-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="Précédent">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($projets->getUrlRange(1, $projets->lastPage()) as $page => $url)
                            @if ($page == $projets->currentPage())
                                <span aria-current="page">
                                    <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white border border-orange-500 cursor-default leading-5" style="background: linear-gradient(135deg, var(--orange-primary), var(--success-700));">{{ $page }}</span>
                                </span>
                            @else
                                <button onclick="changePage({{ $page }})" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-orange-300 focus:border-orange-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150" aria-label="Page {{ $page }}">
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($projets->hasMorePages())
                            <button onclick="changePage({{ $projets->currentPage() + 1 }})" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-orange-300 focus:border-orange-300 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="Suivant">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @else
                            <span aria-disabled="true" aria-label="Suivant">
                                <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-r-md leading-5" aria-hidden="true">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>
@endif

<script>
function viewProject(projectId) {
    // Rediriger vers la page de détail du projet ou ouvrir une modal
    // À implémenter selon vos besoins
    console.log('Voir le projet:', projectId);
    alert('Fonctionnalité de visualisation du projet à implémenter');
}
</script>