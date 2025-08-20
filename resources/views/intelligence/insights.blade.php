<!-- Vue partielle pour les insights -->
<div class="space-y-8">
    <!-- Statistiques Principales -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_projets']) }}</div>
                    <div class="text-sm text-gray-600">Projets Totaux</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_entrepreneurs']) }}</div>
                    <div class="text-sm text-gray-600">Entrepreneurs</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['projets_formalises']) }}</div>
                    <div class="text-sm text-gray-600">Projets Formalisés</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['projets_non_formalises']) }}</div>
                    <div class="text-sm text-gray-600">En Cours de Formalisation</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Répartition par Région -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Répartition par Région</h3>
            <div class="space-y-4">
                @foreach($regions as $region)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-gradient-to-r from-orange-400 to-green-400 rounded-full mr-3"></div>
                            <span class="text-sm font-medium text-gray-900">{{ ucfirst($region->region) }}</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                <div class="bg-gradient-to-r from-orange-400 to-green-400 h-2 rounded-full" style="width: {{ ($region->total / $stats['total_projets']) * 100 }}%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 w-10 text-right">{{ $region->total }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Top Secteurs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Secteurs Principaux</h3>
            <div class="space-y-4">
                @foreach($secteurs->take(8) as $secteur => $count)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-gradient-to-r from-blue-400 to-purple-400 rounded-full mr-3"></div>
                            <span class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $secteur)) }}</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-24 bg-gray-200 rounded-full h-2 mr-3">
                                <div class="bg-gradient-to-r from-blue-400 to-purple-400 h-2 rounded-full" style="width: {{ ($count / $secteurs->first()) * 100 }}%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 w-8 text-right">{{ $count }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Maturité et Financement -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Stades de Maturité -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Stades de Maturité</h3>
            <div class="space-y-3">
                @foreach($maturites as $maturite)
                    @if($maturite->maturite)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $maturite->maturite)) }}</span>
                            <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">{{ $maturite->total }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Stades de Financement -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Besoins de Financement</h3>
            <div class="space-y-3">
                @foreach($financements as $financement)
                    @if($financement->stade_financement)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $financement->stade_financement)) }}</span>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">{{ $financement->total }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Évolution Temporelle -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Évolution des Créations (12 derniers mois)</h3>
        <div class="flex items-end space-x-2 h-64 overflow-x-auto">
            @foreach($evolution->reverse() as $month)
                <div class="flex flex-col items-center min-w-0 flex-1">
                    <div class="bg-gradient-to-t from-orange-400 to-green-400 rounded-t w-full flex-shrink-0" style="height: {{ ($month->total / $evolution->max('total')) * 200 }}px; min-height: 20px;"></div>
                    <div class="text-xs text-gray-600 mt-2 text-center">
                        {{ $month->month }}/{{ $month->year }}
                    </div>
                    <div class="text-xs font-semibold text-gray-900">{{ $month->total }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Indicateurs de Performance -->
    <div class="bg-gradient-to-r from-orange-500 to-green-500 rounded-xl shadow-lg p-8 text-white">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="text-3xl font-bold">{{ round(($stats['projets_formalises'] / $stats['total_projets']) * 100, 1) }}%</div>
                <div class="text-orange-100 mt-2">Taux de Formalisation</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold">{{ $regions->count() }}</div>
                <div class="text-orange-100 mt-2">Régions Couvertes</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold">{{ $secteurs->count() }}+</div>
                <div class="text-orange-100 mt-2">Secteurs d'Activité</div>
            </div>
        </div>
    </div>
</div>