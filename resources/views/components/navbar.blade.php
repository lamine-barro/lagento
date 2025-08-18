<header class="fixed top-0 left-0 right-0 z-fixed" style="height: var(--header-height); background: var(--white); border-bottom: 1px solid var(--gray-200);">
    <div class="container max-w-4xl mx-auto flex items-center justify-between h-full">
        <!-- Toggle mobile (Diagnostic / Agent) -->
        <div class="md:hidden flex items-center gap-1 bg-gray-100 rounded-md" style="padding: 2px !important;">
            <a href="{{ route('diagnostic') }}" 
               class="touch-target rounded-md transition-colors {{ request()->routeIs('diagnostic') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}"
               title="Diagnostic">
                <i data-lucide="chart-network" class="w-4 h-4"></i>
            </a>
            <a href="{{ route('chat.index') }}" 
               class="touch-target rounded-md transition-colors {{ request()->routeIs('chat.index') ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}"
               title="Agent">
                <i data-lucide="brain" class="w-4 h-4"></i>
            </a>
        </div>
        
        <!-- Logo -->
        <div class="flex items-center gap-2">
            <a href="{{ route('chat.index') }}">
                <x-logo size="lg" />
            </a>
        </div>
        
        <!-- Navigation desktop -->
        <nav class="hidden md:flex items-center bg-gray-100 rounded-lg p-1">
            <a 
                href="{{ route('diagnostic') }}"
                class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-md transition-colors
                       {{ request()->routeIs('diagnostic') ? 'bg-white shadow-sm text-gray-900' : 'text-gray-600 hover:text-gray-900' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span class="hidden lg:inline">Diagnostic ({{ auth()->user()->getRemainingDiagnostics() }}/3)</span>
            </a>
            <a 
                href="{{ route('chat.index') }}"
                class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-md transition-colors
                       {{ request()->routeIs('chat.index') ? 'bg-white shadow-sm text-gray-900' : 'text-gray-600 hover:text-gray-900' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <span class="hidden lg:inline">Agent</span>
            </a>
        </nav>
        
        <!-- Actions -->
        <div class="flex items-center gap-2">
            <x-theme-toggle />
            <a href="{{ route('profile') }}" class="touch-target rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </a>
        </div>
    </div>
</header>
