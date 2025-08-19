@extends('layouts.guest')

@section('seo_title', 'Assistant IA pour entrepreneurs en Côte d\'Ivoire - LagentO')
@section('meta_description', 'LagentO est le premier assistant IA spécialisé pour entrepreneurs ivoiriens. Conseils business personnalisés, opportunités de financement, diagnostic d\'entreprise et accompagnement 24/7. Gratuit pour startups en Côte d\'Ivoire.')
@section('meta_keywords', 'assistant IA entrepreneur, côte ivoire startup, conseiller business abidjan, assistant entrepreneur ivoirien, IA business africaine, lamine barro agro tech')
@section('canonical_url', route('seo.assistant-ia'))

@section('og_title', 'Assistant IA Entrepreneur Côte d\'Ivoire - LagentO')
@section('og_description', 'Premier assistant IA spécialisé pour entrepreneurs ivoiriens. Conseils business, financement, diagnostic et accompagnement 24/7.')
@section('og_type', 'website')

@section('schema_org')
@verbatim
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Assistant IA Entrepreneur Côte d'Ivoire",
    "description": "LagentO est le premier assistant IA spécialisé pour entrepreneurs ivoiriens",
@endverbatim
    "url": "{{ route('seo.assistant-ia') }}",
@verbatim
    "mainEntity": {
        "@type": "SoftwareApplication",
        "name": "LagentO",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "XOF"
        },
        "creator": {
            "@type": "Person",
            "name": "Lamine Barro",
            "jobTitle": "CEO & Founder",
            "worksFor": {
                "@type": "Organization",
                "name": "LagentO Tech"
            }
        },
        "serviceArea": {
            "@type": "Country",
            "name": "Côte d'Ivoire"
        }
    },
    "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Accueil",
@endverbatim
                "item": "{{ url('/') }}"
@verbatim
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "Assistant IA Entrepreneur",
@endverbatim
                "item": "{{ route('seo.assistant-ia') }}"
@verbatim
            }
        ]
    }
}
@endverbatim
@endsection

@section('page_title', 'Assistant IA Entrepreneur Côte d\'Ivoire')
@section('title', 'Assistant IA Entrepreneur Côte d\'Ivoire')

@section('content')
<div class="min-h-screen" style="background: var(--gray-50);">
    
    <!-- Hero Section -->
    <section class="py-20" style="background: linear-gradient(135deg, var(--orange-primary) 0%, var(--orange-light) 100%);">
        <div class="container max-w-4xl mx-auto px-4 text-center text-white">
            <h1 class="text-4xl font-bold mb-6">
                LagentO - Assistant IA pour entrepreneurs ivoiriens
            </h1>
            <p class="text-xl mb-8 opacity-90">
                Un assistant intelligence artificielle conçu pour accompagner les entrepreneurs et startups en Côte d'Ivoire
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('landing') }}" class="btn btn-lg px-8 py-4 text-lg" style="background: white; color: var(--orange-primary);">
                    🚀 Commencer Gratuitement
                </a>
                <a href="{{ route('seo.diagnostic') }}" class="btn btn-lg px-8 py-4 text-lg" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">
                    📊 Diagnostic Gratuit
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20">
        <div class="container max-w-6xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--gray-900);">
                    Pourquoi choisir LagentO en Côte d'Ivoire ?
                </h2>
                <p class="text-lg" style="color: var(--gray-700);">
                    Une expertise IA adaptée aux défis des entrepreneurs en Côte d'Ivoire
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Feature 1 -->
                <div class="card p-6 text-center">
                    <div class="text-4xl mb-4">🧠</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Intelligence artificielle spécialisée</h3>
                    <p style="color: var(--gray-700);">IA entraînée sur l'écosystème entrepreneurial ivoirien, les réglementations OHADA et les opportunités locales</p>
                </div>

                <!-- Feature 2 -->
                <div class="card p-6 text-center">
                    <div class="text-4xl mb-4">💰</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Financement et opportunités</h3>
                    <p style="color: var(--gray-700);">Accès instantané aux opportunités de financement, subventions et concours pour startups en Côte d'Ivoire</p>
                </div>

                <!-- Feature 3 -->
                <div class="card p-6 text-center">
                    <div class="text-4xl mb-4">⚡</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Disponible 24h/24</h3>
                    <p style="color: var(--gray-700);">Assistance entrepreneuriale continue, conseils business instantanés et support personnalisé jour et nuit</p>
                </div>

                <!-- Feature 4 -->
                <div class="card p-6 text-center">
                    <div class="text-4xl mb-4">📊</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Diagnostic d'entreprise</h3>
                    <p style="color: var(--gray-700);">Analyse approfondie de votre projet, recommandations stratégiques et plan d'action personnalisé</p>
                </div>

                <!-- Feature 5 -->
                <div class="card p-6 text-center">
                    <div class="text-4xl mb-4">🌍</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Expertise locale</h3>
                    <p style="color: var(--gray-700);">Connaissance approfondie du marché ivoirien, réseaux d'affaires et écosystème startup local</p>
                </div>

                <!-- Feature 6 -->
                <div class="card p-6 text-center">
                    <div class="text-4xl mb-4">🚀</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Accompagnement complet</h3>
                    <p style="color: var(--gray-700);">De l'idée au succès : formalisation, stratégie, marketing, recrutement et levée de fonds</p>
                </div>

            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16" style="background: var(--gray-100);">
        <div class="container max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-12" style="color: var(--gray-900);">
                LagentO en chiffres
            </h2>
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <div class="text-3xl font-bold mb-2" style="color: var(--orange-primary);">120K+</div>
                    <div style="color: var(--gray-700);">Jeunes accompagnés</div>
                </div>
                <div>
                    <div class="text-3xl font-bold mb-2" style="color: var(--orange-primary);">500+</div>
                    <div style="color: var(--gray-700);">Organisations partenaires</div>
                </div>
                <div>
                    <div class="text-3xl font-bold mb-2" style="color: var(--orange-primary);">36</div>
                    <div style="color: var(--gray-700);">Pays en Afrique</div>
                </div>
                <div>
                    <div class="text-3xl font-bold mb-2" style="color: var(--orange-primary);">24/7</div>
                    <div style="color: var(--gray-700);">Support disponible</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20">
        <div class="container max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-6" style="color: var(--gray-900);">
                Prêt à faire grandir votre entreprise ?
            </h2>
            <p class="text-lg mb-8" style="color: var(--gray-700);">
                Rejoignez les milliers d'entrepreneurs ivoiriens qui font confiance à LagentO
            </p>
            <a href="{{ route('landing') }}" class="btn btn-primary btn-lg px-8 py-4 text-lg">
                🚀 Démarrer maintenant - C'est gratuit !
            </a>
        </div>
    </section>

</div>
@endsection