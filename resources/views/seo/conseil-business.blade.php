@extends('layouts.guest')

@section('seo_title', 'Conseil business et innovation en Côte d\'Ivoire - Agento')
@section('meta_description', 'Conseil business expert pour entrepreneurs africains avec Agento. Stratégie d\'innovation, développement commercial, transformation digitale et croissance en Afrique. Expertise L'équipe Agento.')
@section('meta_keywords', 'conseil business afrique, innovation entrepreneur, stratégie commerciale ci, consultant business abidjan, développement entreprise ivoirienne')
@section('canonical_url', route('seo.conseil'))

@section('og_title', 'Conseil Business Innovation Afrique - Agento')
@section('og_description', 'Conseil business expert et innovation pour entrepreneurs africains. Stratégie, croissance et transformation digitale avec Agento.')
@section('og_type', 'website')

@section('schema_org')
@verbatim
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Conseil Business Innovation Afrique",
    "description": "Services de conseil business et innovation pour entrepreneurs africains",
@endverbatim
    "url": "{{ route('seo.conseil') }}",
@verbatim
    "mainEntity": {
        "@type": "ProfessionalService",
        "name": "Conseil Business Innovation",
        "provider": {
            "@type": "Organization",
            "name": "Agento",
            "founder": {
                "@type": "Person",
                "name": "L'équipe Agento",
                "jobTitle": "Expert Business & Innovation",
                "description": "Expert Innovation & Tech, Ambassadeur Innovation Côte d'Ivoire"
            }
        },
        "serviceType": "Business Consulting",
        "areaServed": {
            "@type": "Continent",
            "name": "Africa"
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
                "name": "Conseil Business",
@endverbatim
                "item": "{{ route('seo.conseil') }}"
@verbatim
            }
        ]
    }
}
@endverbatim
@endsection

@section('page_title', 'Conseil Business Innovation Afrique')
@section('title', 'Conseil Business Innovation Afrique')

@section('content')
<div class="min-h-screen" style="background: var(--gray-50);">
    
    <!-- Hero Section -->
    <section class="py-20" style="background: linear-gradient(135deg, var(--purple-600) 0%, var(--purple-500) 100%);">
        <div class="container max-w-4xl mx-auto px-4 text-center text-white">
            <h1 class="text-4xl font-bold mb-6">
                🚀 Conseil business et innovation en Côte d'Ivoire
            </h1>
            <p class="text-xl mb-8 opacity-90">
                L'expertise de L'équipe Agento et Agento pour développer votre entreprise en Côte d'Ivoire et en Afrique
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('landing') }}" class="btn btn-lg px-8 py-4 text-lg" style="background: white; color: var(--purple-600);">
                    🎯 Obtenir un Conseil Expert
                </a>
                <a href="{{ route('seo.diagnostic') }}" class="btn btn-lg px-8 py-4 text-lg" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">
                    📊 Diagnostic Préalable
                </a>
            </div>
        </div>
    </section>

    <!-- Expertise Section -->
    <section class="py-20">
        <div class="container max-w-6xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--gray-900);">
                    L'expérience de L'équipe Agento au service des entrepreneurs
                </h2>
                <p class="text-lg" style="color: var(--gray-700);">
                    Expert Business & Tech, Ambassadeur Innovation CI, 15M+ entrepreneurs accompagnés en Afrique
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-12 items-center mb-16">
                <div>
                    <h3 class="text-2xl font-semibold mb-6" style="color: var(--gray-900);">
                        🏆 Expérience éprouvée
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <span class="text-2xl mr-4">✨</span>
                            <div>
                                <strong>500+ Organisations</strong> accompagnées en Afrique
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="text-2xl mr-4">🌍</span>
                            <div>
                                <strong>36 Pays africains</strong> de rayonnement
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="text-2xl mr-4">🏅</span>
                            <div>
                                <strong>Président Ci20</strong> - Collège des startups ivoiriennes
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="text-2xl mr-4">🎓</span>
                            <div>
                                <strong>Executive MBA HEC Paris</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="bg-white rounded-lg shadow-lg p-8">
                        <div class="text-6xl mb-4">👨‍💼</div>
                        <h4 class="text-2xl font-semibold mb-2" style="color: var(--gray-900);">L'équipe Agento</h4>
                        <p class="text-sm mb-4" style="color: var(--gray-700);">
                            Entrepreneur Tech, Expert Innovation<br>
                            Ambassadeur Innovation Côte d'Ivoire
                        </p>
                        <div class="text-center">
                            <div class="text-2xl font-bold" style="color: var(--purple-600);">17M€</div>
                            <div class="text-xs" style="color: var(--gray-600);">CA généré par les startups Ci20</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services de Conseil -->
    <section class="py-16" style="background: var(--gray-100);">
        <div class="container max-w-6xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-12 text-center" style="color: var(--gray-900);">
                Services de conseil business
            </h2>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <div class="card p-6">
                    <div class="text-4xl mb-4">📈</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Stratégie de croissance</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Développement de stratégies adaptées au marché ivoirien et africain</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Analyse concurrentielle</li>
                        <li>• Expansion géographique</li>
                        <li>• Diversification produits</li>
                    </ul>
                </div>

                <div class="card p-6">
                    <div class="text-4xl mb-4">🔬</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Innovation et R&D</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Intégration de l'innovation dans votre modèle d'affaires</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Stratégie innovation</li>
                        <li>• Développement produits</li>
                        <li>• Partenariats tech</li>
                    </ul>
                </div>

                <div class="card p-6">
                    <div class="text-4xl mb-4">💻</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Transformation digitale</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Accompagnement dans la digitalisation de vos processus</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Audit digital</li>
                        <li>• Choix technologiques</li>
                        <li>• Conduite du changement</li>
                    </ul>
                </div>

                <div class="card p-6">
                    <div class="text-4xl mb-4">💰</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Stratégie financière</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Optimisation de la structure financière et levée de fonds</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Business plan investisseurs</li>
                        <li>• Préparation due diligence</li>
                        <li>• Négociation financement</li>
                    </ul>
                </div>

                <div class="card p-6">
                    <div class="text-4xl mb-4">🌐</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Développement international</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Expansion en Afrique et à l'international</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Étude de marchés</li>
                        <li>• Partenariats locaux</li>
                        <li>• Stratégie d'entrée</li>
                    </ul>
                </div>

                <div class="card p-6">
                    <div class="text-4xl mb-4">👥</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Leadership et management</h3>
                    <p style="color: var(--gray-700);" class="mb-4">Développement des compétences managériales</p>
                    <ul class="text-sm space-y-1" style="color: var(--gray-600);">
                        <li>• Coaching dirigeants</li>
                        <li>• Structuration équipes</li>
                        <li>• Culture d'entreprise</li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- Méthodologie -->
    <section class="py-20">
        <div class="container max-w-4xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-12 text-center" style="color: var(--gray-900);">
                Méthodologie d'Accompagnement Agento
            </h2>
            
            <div class="grid md:grid-cols-5 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-xl font-bold" style="background: var(--purple-100); color: var(--purple-600);">1</div>
                    <h3 class="font-semibold mb-2" style="color: var(--gray-900);">Audit 360°</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Diagnostic complet IA + expertise humaine</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-xl font-bold" style="background: var(--purple-100); color: var(--purple-600);">2</div>
                    <h3 class="font-semibold mb-2" style="color: var(--gray-900);">Stratégie</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Co-construction de la stratégie optimale</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-xl font-bold" style="background: var(--purple-100); color: var(--purple-600);">3</div>
                    <h3 class="font-semibold mb-2" style="color: var(--gray-900);">Roadmap</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Plan d'action détaillé et priorisé</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-xl font-bold" style="background: var(--purple-100); color: var(--purple-600);">4</div>
                    <h3 class="font-semibold mb-2" style="color: var(--gray-900);">Exécution</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Accompagnement dans la mise en œuvre</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-xl font-bold" style="background: var(--purple-100); color: var(--purple-600);">5</div>
                    <h3 class="font-semibold mb-2" style="color: var(--gray-900);">Suivi</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Mesure des résultats et ajustements</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Cas d'Usage -->
    <section class="py-16" style="background: var(--gray-100);">
        <div class="container max-w-6xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-12 text-center" style="color: var(--gray-900);">
                Cas d'Usage & Secteurs d'Expertise
            </h2>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-lg p-6">
                    <h3 class="text-xl font-semibold mb-4" style="color: var(--gray-900);">🏢 Startups en Croissance</h3>
                    <ul class="space-y-2 text-sm" style="color: var(--gray-700);">
                        <li>• Structuration organisation</li>
                        <li>• Préparation levée de fonds</li>
                        <li>• Passage à l'échelle</li>
                        <li>• Expansion géographique</li>
                    </ul>
                </div>
                
                <div class="bg-white rounded-lg p-6">
                    <h3 class="text-xl font-semibold mb-4" style="color: var(--gray-900);">🏭 PME Traditionnelles</h3>
                    <ul class="space-y-2 text-sm" style="color: var(--gray-700);">
                        <li>• Transformation digitale</li>
                        <li>• Modernisation processus</li>
                        <li>• Nouvelles opportunités</li>
                        <li>• Optimisation coûts</li>
                    </ul>
                </div>
                
                <div class="bg-white rounded-lg p-6">
                    <h3 class="text-xl font-semibold mb-4" style="color: var(--gray-900);">🌍 Expansion Afrique</h3>
                    <ul class="space-y-2 text-sm" style="color: var(--gray-700);">
                        <li>• Étude de marché multi-pays</li>
                        <li>• Stratégies d'entrée</li>
                        <li>• Partenariats stratégiques</li>
                        <li>• Adaptation culturelle</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20">
        <div class="container max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-6" style="color: var(--gray-900);">
                Prêt à transformer votre entreprise ?
            </h2>
            <p class="text-lg mb-8" style="color: var(--gray-700);">
                Bénéficiez de l'expertise de L'équipe Agento et de l'intelligence artificielle Agento pour accélérer votre croissance
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('landing') }}" class="btn btn-primary btn-lg px-8 py-4 text-lg">
                    🚀 Démarrer un Conseil Expert
                </a>
                <a href="{{ route('seo.diagnostic') }}" class="btn btn-outline-primary btn-lg px-8 py-4 text-lg">
                    📊 Commencer par un Diagnostic
                </a>
            </div>
        </div>
    </section>

</div>
@endsection