@extends('layouts.guest')

@section('seo_title', 'Diagnostic gratuit d\'entreprise en Côte d\'Ivoire - LAgentO')
@section('meta_description', 'Obtenez un diagnostic complet et gratuit de votre entreprise en Côte d\'Ivoire avec LAgentO. Analyse stratégique, recommandations personnalisées et plan d\'action pour développer votre business à Abidjan.')
@section('meta_keywords', 'diagnostic entreprise gratuit, analyse business ci, évaluation startup abidjan, audit stratégique côte ivoire, conseil entreprise ivoirien')
@section('canonical_url', route('seo.diagnostic'))

@section('og_title', 'Diagnostic Entreprise Gratuit CI - LAgentO')
@section('og_description', 'Diagnostic complet et gratuit de votre entreprise en Côte d\'Ivoire. Analyse IA personnalisée avec recommandations stratégiques.')
@section('og_type', 'website')

@section('schema_org')
@verbatim
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Diagnostic Entreprise Gratuit Côte d'Ivoire",
    "description": "Service de diagnostic d'entreprise gratuit par intelligence artificielle en Côte d'Ivoire",
@endverbatim
    "url": "{{ route('seo.diagnostic') }}",
@verbatim
    "mainEntity": {
        "@type": "Service",
        "name": "Diagnostic Entreprise Gratuit",
        "provider": {
            "@type": "Organization",
            "name": "LAgentO",
            "founder": {
                "@type": "Person",
                "name": "Lamine Barro"
            }
        },
        "serviceType": "Business Consulting",
        "areaServed": {
            "@type": "Country",
            "name": "Côte d'Ivoire"
        },
        "offers": {
            "@type": "Offer",
            "name": "Diagnostic Entreprise IA",
            "description": "Analyse complète et recommandations stratégiques",
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
                "name": "Diagnostic Entreprise",
@endverbatim
                "item": "{{ route('seo.diagnostic') }}"
@verbatim
            }
        ]
    }
}
@endverbatim
@endsection

@section('page_title', 'Diagnostic Entreprise Gratuit CI')
@section('title', 'Diagnostic Entreprise Gratuit CI')

@section('content')
<div class="min-h-screen" style="background: var(--gray-50);">
    
    <!-- Hero Section -->
    <section class="py-20" style="background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-500) 100%);">
        <div class="container max-w-4xl mx-auto px-4 text-center text-white">
            <h1 class="text-4xl font-bold mb-6">
                📊 Diagnostic gratuit de votre entreprise en Côte d'Ivoire
            </h1>
            <p class="text-xl mb-8 opacity-90">
                Une analyse complète de votre entreprise par l'intelligence artificielle LAgentO, adaptée au contexte ivoirien
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('landing') }}" class="btn btn-lg px-8 py-4 text-lg" style="background: white; color: var(--blue-600);">
                    🚀 Commencer mon Diagnostic
                </a>
                <a href="{{ route('seo.assistant-ia') }}" class="btn btn-lg px-8 py-4 text-lg" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">
                    🤖 Découvrir LAgentO
                </a>
            </div>
        </div>
    </section>

    <!-- Qu'est-ce que le diagnostic -->
    <section class="py-20">
        <div class="container max-w-4xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-6" style="color: var(--gray-900);">
                    Comment fonctionne le diagnostic LAgentO ?
                </h2>
                <p class="text-lg" style="color: var(--gray-700);">
                    Une analyse complète de votre entreprise par intelligence artificielle, adaptée au marché ivoirien
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h3 class="text-2xl font-semibold mb-4" style="color: var(--gray-900);">
                        Analyse en 15 minutes
                    </h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <span class="text-xl mr-3">✅</span>
                            <div>
                                <strong>Évaluation stratégique</strong><br>
                                <span style="color: var(--gray-700);">Position concurrentielle, forces et faiblesses</span>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <span class="text-xl mr-3">✅</span>
                            <div>
                                <strong>Opportunités de marché</strong><br>
                                <span style="color: var(--gray-700);">Niches inexploitées et tendances sectorielles CI</span>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <span class="text-xl mr-3">✅</span>
                            <div>
                                <strong>Plan d'action personnalisé</strong><br>
                                <span style="color: var(--gray-700);">Étapes concrètes pour développer votre business</span>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <span class="text-xl mr-3">✅</span>
                            <div>
                                <strong>Recommandations financières</strong><br>
                                <span style="color: var(--gray-700);">Sources de financement adaptées à votre profil</span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="text-center">
                    <div class="bg-white rounded-lg shadow-lg p-8">
                        <div class="text-6xl mb-4">🎯</div>
                        <h4 class="text-xl font-semibold mb-2" style="color: var(--gray-900);">100% Gratuit</h4>
                        <p class="text-sm" style="color: var(--gray-700);">
                            Aucun engagement, résultats instantanés
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('landing') }}" class="btn btn-primary px-6 py-3">
                                Démarrer maintenant
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Comment ça marche -->
    <section class="py-16" style="background: var(--gray-100);">
        <div class="container max-w-6xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-12 text-center" style="color: var(--gray-900);">
                Comment fonctionne le diagnostic LAgentO ?
            </h2>
            
            <div class="grid md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center text-3xl" style="background: var(--blue-100); color: var(--blue-600);">📝</div>
                    <h3 class="text-lg font-semibold mb-3" style="color: var(--gray-900);">1. Questionnaire Intelligent</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Questions adaptées à votre secteur d'activité et contexte ivoirien</p>
                </div>
                
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center text-3xl" style="background: var(--blue-100); color: var(--blue-600);">🤖</div>
                    <h3 class="text-lg font-semibold mb-3" style="color: var(--gray-900);">2. Analyse IA</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Intelligence artificielle spécialisée analyse vos réponses en temps réel</p>
                </div>
                
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center text-3xl" style="background: var(--blue-100); color: var(--blue-600);">📊</div>
                    <h3 class="text-lg font-semibold mb-3" style="color: var(--gray-900);">3. Rapport Détaillé</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Diagnostic complet avec scores, analyses et visualisations</p>
                </div>
                
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center text-3xl" style="background: var(--blue-100); color: var(--blue-600);">🎯</div>
                    <h3 class="text-lg font-semibold mb-3" style="color: var(--gray-900);">4. Plan d'Action</h3>
                    <p class="text-sm" style="color: var(--gray-700);">Recommandations concrètes et priorisées pour votre croissance</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Domaines analysés -->
    <section class="py-20">
        <div class="container max-w-6xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-4" style="color: var(--gray-900);">
                    Domaines Analysés par le Diagnostic
                </h2>
                <p class="text-lg" style="color: var(--gray-700);">
                    Une évaluation complète sur tous les aspects critiques de votre entreprise
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <div class="card p-6 text-center">
                    <div class="text-4xl mb-4">💼</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Stratégie Business</h3>
                    <p style="color: var(--gray-700);">Modèle économique, positionnement, proposition de valeur</p>
                </div>

                <div class="card p-6 text-center">
                    <div class="text-4xl mb-4">📈</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Performance Financière</h3>
                    <p style="color: var(--gray-700);">Rentabilité, trésorerie, besoins de financement</p>
                </div>

                <div class="card p-6 text-center">
                    <div class="text-4xl mb-4">🎯</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Marketing & Ventes</h3>
                    <p style="color: var(--gray-700);">Stratégie commerciale, acquisition clients, canaux</p>
                </div>

                <div class="card p-6 text-center">
                    <div class="text-4xl mb-4">⚙️</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Opérations</h3>
                    <p style="color: var(--gray-700);">Processus, efficacité, qualité, supply chain</p>
                </div>

                <div class="card p-6 text-center">
                    <div class="text-4xl mb-4">👥</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Ressources Humaines</h3>
                    <p style="color: var(--gray-700);">Équipe, compétences, culture d'entreprise</p>
                </div>

                <div class="card p-6 text-center">
                    <div class="text-4xl mb-4">🔧</div>
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--gray-900);">Innovation & Tech</h3>
                    <p style="color: var(--gray-700);">Transformation digitale, innovation produit</p>
                </div>

            </div>
        </div>
    </section>

    <!-- Témoignages -->
    <section class="py-16" style="background: var(--gray-100);">
        <div class="container max-w-4xl mx-auto px-4">
            <h2 class="text-3xl font-bold mb-12 text-center" style="color: var(--gray-900);">
                Ce que disent les entrepreneurs ivoiriens
            </h2>
            
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-xl" style="background: var(--orange-100);">👨‍💼</div>
                        <div class="ml-4">
                            <h4 class="font-semibold" style="color: var(--gray-900);">Kouassi M.</h4>
                            <p class="text-sm" style="color: var(--gray-600);">Startup AgriTech, Abidjan</p>
                        </div>
                    </div>
                    <p style="color: var(--gray-700);">"Le diagnostic LAgentO m'a aidé à identifier les vraies opportunités de mon secteur en CI. Les recommandations étaient très précises et adaptées au contexte local."</p>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-xl" style="background: var(--orange-100);">👩‍💼</div>
                        <div class="ml-4">
                            <h4 class="font-semibold" style="color: var(--gray-900);">Aminata D.</h4>
                            <p class="text-sm" style="color: var(--gray-600);">PME Commerce, Bouaké</p>
                        </div>
                    </div>
                    <p style="color: var(--gray-700);">"Grâce au diagnostic, j'ai restructuré mon business model et augmenté mon chiffre d'affaires de 40% en 6 mois. Un outil indispensable !"</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20">
        <div class="container max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-6" style="color: var(--gray-900);">
                Prêt à analyser votre entreprise ?
            </h2>
            <p class="text-lg mb-8" style="color: var(--gray-700);">
                Découvrez les points forts et axes d'amélioration de votre entreprise
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('landing') }}" class="btn btn-primary btn-lg px-8 py-4 text-lg">
                    📊 Commencer mon diagnostic gratuit
                </a>
                <a href="{{ route('seo.financement') }}" class="btn btn-outline-primary btn-lg px-8 py-4 text-lg">
                    💰 Explorer les financements
                </a>
            </div>
        </div>
    </section>

</div>
@endsection