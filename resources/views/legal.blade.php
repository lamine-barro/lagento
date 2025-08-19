@extends('layouts.guest')

@section('title', 'Mentions légales')

@section('content')
<div class="min-h-screen bg-white">
    <!-- Header -->
    <div class="sticky top-0 z-10 bg-white border-b p-4" style="border-color: var(--gray-100);">
        <div class="flex items-center gap-3">
            <button onclick="history.back()" class="btn btn-ghost p-2">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </button>
            <h1 class="text-xl font-medium" style="color: var(--gray-900);">Mentions légales</h1>
        </div>
    </div>

    <!-- Content -->
    <div class="p-4 max-w-4xl mx-auto">
        <div class="prose prose-sm max-w-none" style="color: var(--gray-700);">
            
            <!-- Éditeur -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">Éditeur du site</h2>
                <div class="space-y-2">
                    <p><strong>Organisation :</strong> Think Tank Horizon O</p>
                    <p><strong>Mission :</strong> Ensemble, construisons l'horizon entrepreneurial de la Côte d'Ivoire</p>
                    <p><strong>Produit :</strong> LagentO - Assistant IA développé par Horizon O</p>
                    <p><strong>Siège social :</strong> CHU d'Angré, Abidjan, Côte d'Ivoire</p>
                    <p><strong>Boîte postale :</strong> 28 BP 942 ABJ 28</p>
                    <p><strong>Email :</strong> info@horizon-o.ci</p>
                    <p><strong>Téléphone :</strong> 01 72 939 595 / 07 47 94 42 22</p>
                    <p><strong>Directeur de publication :</strong> Équipe Horizon O</p>
                </div>
            </section>

            <!-- Hébergement -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">Hébergement</h2>
                <div class="space-y-2">
                    <p><strong>Hébergeur :</strong> Amazon Web Services (AWS)</p>
                    <p><strong>Adresse :</strong> Amazon Web Services, Inc., 410 Terry Avenue North, Seattle, WA 98109, États-Unis</p>
                    <p><strong>Téléphone :</strong> +1 206-266-1000</p>
                </div>
            </section>

            <!-- Propriété intellectuelle -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">Propriété intellectuelle</h2>
                <p class="mb-4">
                    L'ensemble du site Agent O, incluant mais non limité aux textes, images, sons, logiciels, 
                    et tout autre élément composant le site, est la propriété exclusive de Think Tank Horizon O, 
                    sauf mention contraire.
                </p>
                <p class="mb-4">
                    Toute reproduction, représentation, modification, publication, adaptation de tout ou partie 
                    des éléments du site, quel que soit le moyen ou le procédé utilisé, est interdite, 
                    sauf autorisation écrite préalable de Think Tank Horizon O.
                </p>
                <p>
                    La marque "Agent O" et le logo associé sont des marques déposées de Think Tank Horizon O.
                </p>
            </section>

            <!-- Protection des données -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">Protection des données personnelles</h2>
                
                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">Responsable de traitement</h3>
                <p class="mb-4">
                    Think Tank Horizon O est responsable du traitement 
                    de vos données personnelles.
                </p>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">Données collectées</h3>
                <ul class="list-disc list-inside mb-4 space-y-1">
                    <li>Données d'identification : nom, prénom, adresse email</li>
                    <li>Données professionnelles : entreprise, secteur d'activité, poste</li>
                    <li>Données de navigation : logs de connexion, adresse IP</li>
                    <li>Données de conversation : messages échangés avec l'IA</li>
                </ul>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">Finalités du traitement</h3>
                <ul class="list-disc list-inside mb-4 space-y-1">
                    <li>Fourniture du service Agent O</li>
                    <li>Personnalisation des recommandations</li>
                    <li>Amélioration du service</li>
                    <li>Communication commerciale (avec consentement)</li>
                    <li>Respect des obligations légales</li>
                </ul>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">Base légale</h3>
                <p class="mb-4">
                    Le traitement de vos données repose sur l'exécution du contrat de service, 
                    l'intérêt légitime de Think Tank Horizon O, et le cas échéant votre consentement.
                </p>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">Durée de conservation</h3>
                <p class="mb-4">
                    Vos données sont conservées pendant la durée nécessaire aux finalités 
                    pour lesquelles elles sont traitées, et au maximum 3 ans après la fin 
                    de la relation contractuelle.
                </p>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">Vos droits</h3>
                <p class="mb-2">Conformément au Règlement général sur la protection des données (RGPD), vous disposez des droits suivants :</p>
                <ul class="list-disc list-inside mb-4 space-y-1">
                    <li>Droit d'accès à vos données</li>
                    <li>Droit de rectification</li>
                    <li>Droit à l'effacement</li>
                    <li>Droit à la portabilité</li>
                    <li>Droit d'opposition</li>
                    <li>Droit à la limitation du traitement</li>
                </ul>
                <p class="mb-4">
                    Pour exercer ces droits, contactez-nous à : info@horizon-o.ci
                </p>
            </section>

            <!-- Cookies -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">Cookies</h2>
                <p class="mb-4">
                    Le site Agent O utilise des cookies techniques nécessaires au fonctionnement 
                    du service et des cookies d'analyse pour améliorer votre expérience.
                </p>
                <p class="mb-4">
                    Vous pouvez configurer votre navigateur pour refuser les cookies, 
                    mais cela peut affecter le bon fonctionnement du site.
                </p>
            </section>

            <!-- Responsabilité -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">Limitation de responsabilité</h2>
                <p class="mb-4">
                    Think Tank Horizon O s'efforce de fournir des informations exactes et à jour sur Agent O. 
                    Cependant, nous ne pouvons garantir l'exactitude, la complétude ou l'actualité 
                    des informations fournies par l'IA.
                </p>
                <p class="mb-4">
                    Les conseils fournis par Agent O ne constituent pas des conseils juridiques, 
                    financiers ou professionnels. Il est recommandé de consulter des professionnels 
                    qualifiés pour vos besoins spécifiques.
                </p>
                <p>
                    Think Tank Horizon O ne saurait être tenue responsable des dommages directs ou indirects 
                    résultant de l'utilisation du service Agent O.
                </p>
            </section>

            <!-- IA et contenu généré -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">Intelligence artificielle et contenu généré</h2>
                <p class="mb-4">
                    Agent O utilise des technologies d'intelligence artificielle pour générer 
                    des réponses et des recommandations. Le contenu généré peut contenir des erreurs 
                    ou des inexactitudes.
                </p>
                <p class="mb-4">
                    Les utilisateurs sont responsables de vérifier l'exactitude des informations 
                    fournies avant de prendre des décisions basées sur ces informations.
                </p>
                <p>
                    Think Tank Horizon O améliore continuellement ses algorithmes d'IA, mais ne garantit 
                    pas la perfection du contenu généré.
                </p>
            </section>

            <!-- Droit applicable -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">Droit applicable et juridiction</h2>
                <p class="mb-4">
                    Les présentes mentions légales sont régies par le droit ivoirien et le droit OHADA.
                </p>
                <p>
                    En cas de litige, les tribunaux d'Abidjan seront seuls compétents.
                </p>
            </section>

            <!-- Contact -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">Contact</h2>
                <p class="mb-2">Pour toute question concernant ces mentions légales :</p>
                <div class="space-y-1">
                    <p><strong>Email :</strong> info@horizon-o.ci</p>
                    <p><strong>Téléphone :</strong> 01 72 939 595 / 07 47 94 42 22</p>
                    <p><strong>Adresse :</strong> CHU d'Angré, Abidjan, Côte d'Ivoire</p>
                </div>
            </section>

            <!-- Mise à jour -->
            <section class="mb-8">
                <p class="text-sm" style="color: var(--gray-500);">
                    <strong>Dernière mise à jour :</strong> {{ date('d/m/Y') }}
                </p>
            </section>

        </div>
    </div>
</div>
@endsection