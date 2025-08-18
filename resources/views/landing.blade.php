@extends('layouts.guest')

@section('seo_title', 'LAgentO - Assistant IA Entrepreneurial N°1 en Côte d\'Ivoire')
@section('meta_description', 'Découvrez LAgentO, le premier assistant IA spécialement conçu pour accompagner les entrepreneurs ivoiriens. Conseils personnalisés, opportunités de financement, réseau d\'affaires et accompagnement 24/7 pour réussir votre entreprise en Côte d\'Ivoire.')
@section('meta_keywords', 'assistant IA côte ivoire, entrepreneur abidjan, startup CI, financement pme, conseil business, innovation afrique, lamine barro, etudesk')
@section('og_title', 'LAgentO - Votre Assistant IA Entrepreneurial en Côte d\'Ivoire')
@section('og_description', 'Rejoignez plus de 15M+ entrepreneurs qui font confiance à LAgentO pour développer leur business en Afrique. Gratuit et disponible 24/7.')
@section('canonical_url', route('landing'))
@section('title', 'Agent O - Assistant IA Entrepreneurial')

@section('vite')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endsection

@push('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
@endpush

@section('schema_org', json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => 'LAgentO',
    'alternateName' => 'Agent O',
    'description' => 'Premier assistant IA entrepreneurial pour la Côte d\'Ivoire',
    'url' => url('/'),
    'sameAs' => [
        'https://linkedin.com/company/lagento',
        'https://twitter.com/LAgentO_CI'
    ],
    'offers' => [
        '@type' => 'Offer',
        'price' => '0',
        'priceCurrency' => 'XOF',
        'availability' => 'https://schema.org/InStock',
        'description' => 'Assistant IA gratuit pour entrepreneurs'
    ],
    'audience' => [
        '@type' => 'Audience',
        'audienceType' => 'Entrepreneurs',
        'geographicArea' => [
            '@type' => 'Country',
            'name' => 'Côte d\'Ivoire',
            'alternateName' => 'Ivory Coast'
        ]
    ],
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => url('/projets') . '?search={search_term_string}',
        'query-input' => 'required name=search_term_string'
    ]
]))

@section('content')
<div class="min-h-screen flex flex-col bg-white" style="background: linear-gradient(180deg, #fff 0%, #fff7f2 100%);" data-dark-bg>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-2xl text-center">
            <!-- Logo adaptive -->
            <div class="mx-auto mb-6">
                <x-logo size="xl" class="mx-auto" />
            </div>
            
            <!-- Subtitle -->
            <p class="text-lg mb-8 mt-4" style="color: var(--gray-700);">
                LagentO a cartographié plus de 2000 milliards de Fcfa d'opportunités pour les jeunes entrepreneurs en Côte d'Ivoire a travers les initiatives gouvernementales et écosystèmes. Il est disposé 24h/7 pour vous accompagner à les saisir.
            </p>

            <!-- Email Form -->
            <form method="POST" action="{{ route('auth.email') }}" class="flex flex-col gap-4">
                @csrf
                
                <div class="relative">
                    <input 
                        type="email" 
                        name="email" 
                        placeholder="Votre adresse email"
                        value="{{ old('email') }}"
                        class="input-field w-full"
                        required
                        autofocus
                    />
                    @error('email')
                        <p class="text-sm mt-1" style="color: var(--error);">{{ $message }}</p>
                    @enderror
                </div>

                <button 
                    type="submit" 
                    class="btn btn-primary w-full flex items-center justify-center gap-2"
                >
                    Se connecter
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </main>
    
</div>
@endsection