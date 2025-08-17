@php
    $currentStep = $currentStep ?? 1;
    $items = $items ?? [
        ['label' => 'Identité', 'icon' => 'badge-check', 'route' => route('onboarding.step1'), 'step' => 1, 'description' => 'Les informations essentielles de votre entreprise'],
        ['label' => 'Contact', 'icon' => 'user', 'route' => route('onboarding.step2'), 'step' => 2, 'description' => 'Vos coordonnées pour rester connecté'],
        ['label' => 'Activité', 'icon' => 'bar-chart-3', 'route' => route('onboarding.step3'), 'step' => 3, 'description' => 'Votre secteur et vos ambitions'],
        ['label' => 'Équipe', 'icon' => 'users', 'route' => route('onboarding.step4'), 'step' => 4, 'description' => 'Les forces vives de votre projet'],
    ];
    $progressPercent = max(0, min(100, ($currentStep - 1) * 25));
@endphp

<div class="w-full max-w-4xl mx-auto" x-data>
    <!-- Top bar with logo + logout -->
    <div class="flex items-center justify-between py-3 mb-4" style="border-bottom: 1px solid var(--gray-100);">
        <a href="{{ route('diagnostic') }}" class="flex items-center">
            <x-logo size="lg" />
        </a>
        <div class="flex items-center gap-2">
            <x-theme-toggle />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost p-2" title="Déconnexion">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>
    <div class="text-center mb-6">
        <h2 class="text-xl font-semibold" style="color: var(--black);">Configuration du profil</h2>
        <p class="text-sm mt-1" style="color: var(--gray-600);">Étape {{ $currentStep }} sur 4</p>
        <p class="text-sm mt-2" style="color: var(--gray-700);">{{ $items[$currentStep - 1]['description'] }}</p>
    </div>

    <nav class="grid grid-cols-4 gap-3 mb-4">
        @foreach ($items as $item)
            <a href="{{ $item['route'] }}" class="flex flex-col items-center py-3 rounded-md transition-colors"
               style="color: {{ $currentStep === $item['step'] ? 'white' : 'var(--gray-600)' }}; background: {{ $currentStep === $item['step'] ? 'var(--orange)' : 'transparent' }}">
                <i data-lucide="{{ $item['icon'] }}" class="w-6 h-6 mb-1"></i>
                <span class="text-sm font-medium">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div>
        <div class="h-1 rounded-full" style="background: var(--gray-100);">
            <div class="h-1 rounded-full transition-all duration-500" style="background: var(--orange); width: {{ $progressPercent }}%;"></div>
        </div>
    </div>
</div>


