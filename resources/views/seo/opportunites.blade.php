@extends('layouts.guest')

@section('seo_title', 'Opportunités business pour entrepreneurs en Côte d\'Ivoire - LagentO')
@section('meta_description', 'Découvrez toutes les opportunités business, appels d\'offres, concours et partenariats pour entrepreneurs en Côte d\'Ivoire avec LagentO. Veille stratégique et intelligence business en temps réel.')
@section('meta_keywords', 'opportunités business ci, appels offres abidjan, concours entrepreneur ivoirien, partenariats startup, veille business côte ivoire')
@section('canonical_url', route('seo.opportunites'))

@section('og_title', 'Opportunités Entrepreneur Côte d\'Ivoire - LagentO')
@section('og_description', 'Toutes les opportunités business pour entrepreneurs ivoiriens : concours, appels d\'offres, partenariats. Veille intelligente LagentO.')
@section('og_type', 'website')

@section('schema_org')
@verbatim
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Opportunités Entrepreneur Côte d'Ivoire",
    "description": "Plateforme de veille des opportunités business pour entrepreneurs ivoiriens",
@endverbatim
    "url": "{{ route('seo.opportunites') }}",
@verbatim
    "mainEntity": {
        "@type": "Service",
        "name": "Veille Opportunités Business",
        "provider": {
            "@type": "Organization",
            "name": "LagentO"
        },
        "serviceType": "Business Intelligence",
        "areaServed": {
            "@type": "Country",
            "name": "Côte d'Ivoire"
        },
        "offers": {
            "@type": "Offer",
            "name": "Veille Opportunités",
            "description": "Accès aux opportunités business en temps réel",
            "price": "0",
            "priceCurrency": "XOF"
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
                "name": "Opportunités",
@endverbatim
                "item": "{{ route('seo.opportunites') }}"
@verbatim
            }
        ]
    }
}
@endverbatim
@endsection

@section('page_title', 'Opportunités Entrepreneur Côte d\'Ivoire')
@section('title', 'Opportunités Entrepreneur Côte d\'Ivoire')

@section('content')
<div class="min-h-screen" style="background: var(--gray-50);">
    
    <!-- Hero Section -->
    <section class="py-20" style="background: linear-gradient(135deg, var(--teal-600) 0%, var(--teal-500) 100%);">
        <div class="container max-w-4xl mx-auto px-4 text-center text-white">
            <h1 class="text-4xl font-bold mb-6">
                🔍 Opportunités business en Côte d'Ivoire
            </h1>
            <p class="text-xl mb-8 opacity-90">
                LagentO vous aide à découvrir les opportunités business en Côte d'Ivoire : concours, financements, partenariats
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('landing') }}" class="btn btn-lg px-8 py-4 text-lg" style="background: white; color: var(--teal-600);">
                    🚨 Voir les opportunités
                </a>
                <a href="{{ route('seo.financement') }}" class="btn btn-lg px-8 py-4 text-lg" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">
                    💰 Explorer les Financements
                </a>
            </div>
        </div>
    </section>

    <!-- Types d'Opportunités -->
    <section class="py-20">
        <div class="container max-w-6xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--gray-900);">
                    Types d'opportunités en Côte d'Ivoire
                </h2>
                <p class="text-lg" style="color: var(--gray-700);">
                    Découvrez les différents types d'opportunités pour entrepreneurs en Côte d'Ivoire
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <div class="card p-6 border-t-4" style="border-top-color: var(--teal-500);">
                    <div class="text-4xl mb-4">🏆</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Concours et prix</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Compétitions entrepreneuriales avec dotations financières et accompagnement</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Prix innovation Afrique</li>
                        <li>• Concours sectoriels</li>
                        <li>• Awards internationaux</li>
                        <li>• Challenges startup</li>
                    </ul>
                </div>

                <div class="card p-6 border-t-4" style="border-top-color: var(--green-500);">
                    <div class="text-4xl mb-4">📋</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Appels d'Offres</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Marchés publics et privés accessibles aux PME et startups</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Marchés publics CI</li>
                        <li>• Appels d'offres privés</li>
                        <li>• Consultations internationales</li>
                        <li>• Projets ONG/Fondations</li>
                    </ul>
                </div>

                <div class="card p-6 border-t-4" style="border-top-color: var(--blue-500);">
                    <div class="text-4xl mb-4">🤝</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Partenariats</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Opportunités de collaboration et partenariats stratégiques</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Partenariats technologiques</li>
                        <li>• Joint-ventures</li>
                        <li>• Collaborations R&D</li>
                        <li>• Alliances commerciales</li>
                    </ul>
                </div>

                <div class="card p-6 border-t-4" style="border-top-color: var(--purple-500);">
                    <div class="text-4xl mb-4">💼</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Programmes d'Accompagnement</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Incubateurs, accélérateurs et programmes de mentorat</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Incubateurs locaux/internationaux</li>
                        <li>• Programmes sectoriels</li>
                        <li>• Mentoring expert</li>
                        <li>• Bootcamps entrepreneur</li>
                    </ul>
                </div>

                <div class="card p-6 border-t-4" style="border-top-color: var(--orange-500);">
                    <div class="text-4xl mb-4">🌍</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Missions & Événements</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Missions économiques, salons professionnels et networking</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Missions économiques</li>
                        <li>• Salons internationaux</li>
                        <li>• Conférences business</li>
                        <li>• Événements networking</li>
                    </ul>
                </div>

                <div class="card p-6 border-t-4" style="border-top-color: var(--red-500);">
                    <div class="text-4xl mb-4">📚</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Formation & Certification</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Formations professionnelles et certifications sectorielles</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Formations gratuites</li>
                        <li>• Certifications métier</li>
                        <li>• Webinaires experts</li>
                        <li>• Programmes diplômants</li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- Comment ça fonctionne -->
    <section class="py-16" style="background: var(--gray-100);">
        <div class="container max-w-4xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-12 text-center" style="color: var(--gray-900);">
                Comment fonctionne la veille LagentO ?
            </h2>
            
            <div class="grid md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center text-3xl" style="background: var(--teal-100); color: var(--teal-600);">🔍</div>
                    <h3 class="text-lg font-semibold mb-3" style="color: var(--gray-900);">1. Surveillance 24/7</h3>
                    <p class="text-sm" style="color: var(--gray-700);">IA surveille en continu 500+ sources d'opportunités</p>
                </div>
                
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center text-3xl" style="background: var(--teal-100); color: var(--teal-600);">🎯</div>
                    <h3 class="text-lg font-semibold mb-3" style="color: var(--gray-900);">2. Filtrage Intelligent</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Sélection des opportunités selon votre profil et secteur</p>
                </div>
                
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center text-3xl" style="background: var(--teal-100); color: var(--teal-600);">🚨</div>
                    <h3 class="text-lg font-semibold mb-3" style="color: var(--gray-900);">3. Alertes Instantanées</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Notifications push dès qu'une opportunité correspond</p>
                </div>
                
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center text-3xl" style="background: var(--teal-100); color: var(--teal-600);">📋</div>
                    <h3 class="text-lg font-semibold mb-3" style="color: var(--gray-900);">4. Accompagnement</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Aide à la préparation des candidatures et dossiers</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Sources surveillées -->
    <section class="py-20">
        <div class="container max-w-6xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--gray-900);">
                    Sources d'Opportunités Surveillées
                </h2>
                <p class="text-lg" style="color: var(--gray-700);">
                    Plus de 500 sources fiables suivies en temps réel pour ne rien manquer
                </p>
            </div>
            
            <div class="grid md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-3xl mb-3">🏛️</div>
                    <h3 class="font-semibold mb-2" style="color: var(--gray-900);">Institutions Publiques</h3>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>Ministères CI</li>
                        <li>AGEPE</li>
                        <li>FASI</li>
                        <li>Mairies</li>
                    </ul>
                </div>
                
                <div class="text-center">
                    <div class="text-3xl mb-3">🌍</div>
                    <h3 class="font-semibold mb-2" style="color: var(--gray-900);">Organisations Internationales</h3>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>BAD</li>
                        <li>Banque Mondiale</li>
                        <li>ONU</li>
                        <li>Union Européenne</li>
                    </ul>
                </div>
                
                <div class="text-center">
                    <div class="text-3xl mb-3">🏢</div>
                    <h3 class="font-semibold mb-2" style="color: var(--gray-900);">Secteur Privé</h3>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>Grandes entreprises</li>
                        <li>Fonds d'investissement</li>
                        <li>Fondations</li>
                        <li>Incubateurs</li>
                    </ul>
                </div>
                
                <div class="text-center">
                    <div class="text-3xl mb-3">📱</div>
                    <h3 class="font-semibold mb-2" style="color: var(--gray-900);">Plateformes Digitales</h3>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>Sites spécialisés</li>
                        <li>Réseaux sociaux</li>
                        <li>Portails gouvernementaux</li>
                        <li>Médias économiques</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Success Stories -->
    <section class="py-16" style="background: var(--gray-100);">
        <div class="container max-w-4xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-12 text-center" style="color: var(--gray-900);">
                Ils ont saisi leur opportunité avec LagentO
            </h2>
            
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-xl" style="background: var(--teal-100);">🏆</div>
                        <div class="ml-4">
                            <h4 class="font-semibold" style="color: var(--gray-900);">StartupTech CI</h4>
                            <p class="text-sm" style="color: var(--gray-600);">Lauréate Prix Innovation CEDEAO</p>
                        </div>
                    </div>
                    <p style="color: var(--gray-700);">"Grâce à l'alerte LagentO, nous avons postulé au Prix Innovation CEDEAO et remporté 50 000€. Cette opportunité a transformé notre startup."</p>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-xl" style="background: var(--teal-100);">📋</div>
                        <div class="ml-4">
                            <h4 class="font-semibold" style="color: var(--gray-900);">PME Services</h4>
                            <p class="text-sm" style="color: var(--gray-600);">Marché public 200M FCFA</p>
                        </div>
                    </div>
                    <p style="color: var(--gray-700);">"LagentO nous a alertés sur un appel d'offres parfait pour nos services. Nous avons remporté le marché et doublé notre CA."</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20">
        <div class="container max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-6" style="color: var(--gray-900);">
                Besoin d'aide pour trouver des opportunités ?
            </h2>
            <p class="text-lg mb-8" style="color: var(--gray-700);">
                LagentO peut vous aider à identifier les bonnes opportunités pour votre entreprise
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('landing') }}" class="btn btn-primary btn-lg px-8 py-4 text-lg">
                    🚨 Activer ma Veille Opportunités
                </a>
                <a href="{{ route('seo.diagnostic') }}" class="btn btn-outline-primary btn-lg px-8 py-4 text-lg">
                    📊 Profiler mes Besoins
                </a>
            </div>
        </div>
    </section>

</div>
@endsection