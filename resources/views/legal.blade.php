@extends('layouts.guest')

@section('title', 'Mentions légales - Lagento')

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
            
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold mb-2" style="color: var(--gray-900);">MENTIONS LÉGALES - LAGENTO</h1>
            </div>

            <!-- 1. Éditeur -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">1. ÉDITEUR ET RESPONSABLE DE PUBLICATION</h2>
                <div class="space-y-2">
                    <p><strong>Think Tank Horizon O</strong></p>
                    <p><strong>Siège social :</strong> Cocody CHU d'Angré, Abidjan, Côte d'Ivoire</p>
                    <p><strong>Adresse postale :</strong> 28 BP 942 Abidjan 28</p>
                    <p><strong>Email :</strong> info@horizon-o.ci</p>
                    <p><strong>Téléphone :</strong> +225 01 72 93 95 95 / +225 07 47 94 42 22</p>
                    <p><strong>Site web :</strong> www.horizon-o.ci</p>
                    <p><strong>Directeur de publication :</strong> Coordonnateur Général d'Horizon O</p>
                </div>
            </section>

            <!-- 2. Propriété intellectuelle -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">3. PROPRIÉTÉ INTELLECTUELLE</h2>
                
                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">3.1 Droits d'auteur</h3>
                <p class="mb-4">
                    L'ensemble des contenus présents sur la plateforme LagentO (textes, images, vidéos, sons, bases de données, logiciels, marques, logos, etc.) est la propriété exclusive du Think Tank Horizon O ou de ses partenaires, et est protégé par les lois ivoiriennes et internationales relatives à la propriété intellectuelle.
                </p>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">3.2 Marques</h3>
                <p class="mb-4">
                    "LagentO", "Horizon O" et leurs logos associés sont des marques déposées ou en cours d'enregistrement. Toute utilisation non autorisée constitue une contrefaçon passible de poursuites.
                </p>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">3.3 Utilisation du contenu</h3>
                <p class="mb-4">
                    Toute reproduction, représentation, modification, publication ou adaptation de tout ou partie des éléments de la plateforme est strictement interdite sans autorisation écrite préalable d'Horizon O.
                </p>
            </section>

            <!-- 3. Protection des données -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">4. PROTECTION DES DONNÉES PERSONNELLES</h2>
                
                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">4.1 Responsable du traitement</h3>
                <p class="mb-4">
                    Think Tank Horizon O, en sa qualité de responsable du traitement, s'engage à protéger la vie privée des utilisateurs de LagentO conformément à la loi ivoirienne n°2013-450 du 19 juin 2013 relative à la protection des données à caractère personnel.
                </p>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">4.2 Données collectées</h3>
                <ul class="list-disc list-inside mb-4 space-y-1">
                    <li>Données d'identification : nom, prénom, date de naissance, genre</li>
                    <li>Données de contact : email, numéro de téléphone, adresse</li>
                    <li>Données professionnelles : secteur d'activité, type d'entreprise, statut entrepreneurial</li>
                    <li>Données de projet : informations sur le projet entrepreneurial, besoins de financement</li>
                    <li>Données techniques : adresse IP, logs de connexion, type de navigateur</li>
                    <li>Données d'interaction : historique des conversations avec l'IA, diagnostics réalisés</li>
                </ul>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">4.3 Finalités du traitement</h3>
                <ul class="list-disc list-inside mb-4 space-y-1">
                    <li>Fourniture du service d'assistance IA LagentO</li>
                    <li>Diagnostic entrepreneurial personnalisé</li>
                    <li>Matching avec les opportunités de financement</li>
                    <li>Génération de documents personnalisés</li>
                    <li>Amélioration continue du service</li>
                    <li>Statistiques et analyses anonymisées</li>
                    <li>Communication sur les opportunités entrepreneuriales (avec consentement)</li>
                </ul>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">4.4 Base légale</h3>
                <p class="mb-4">Le traitement repose sur :</p>
                <ul class="list-disc list-inside mb-4 space-y-1">
                    <li>L'exécution du service</li>
                    <li>Le consentement explicite de l'utilisateur</li>
                    <li>L'intérêt légitime d'Horizon O pour l'amélioration du service</li>
                    <li>Le respect des obligations légales</li>
                </ul>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">4.5 Durée de conservation</h3>
                <ul class="list-disc list-inside mb-4 space-y-1">
                    <li>Données de compte actif : pendant toute la durée d'utilisation du service</li>
                    <li>Données après clôture : 3 ans maximum</li>
                    <li>Données de connexion : 1 an conformément à la réglementation</li>
                    <li>Documents générés : 5 ans pour les documents commerciaux</li>
                </ul>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">4.6 Vos droits</h3>
                <p class="mb-2">Conformément à la législation en vigueur, vous disposez des droits suivants :</p>
                <ul class="list-disc list-inside mb-4 space-y-1">
                    <li>Droit d'accès à vos données personnelles</li>
                    <li>Droit de rectification des données inexactes</li>
                    <li>Droit à l'effacement ("droit à l'oubli")</li>
                    <li>Droit à la limitation du traitement</li>
                    <li>Droit à la portabilité des données</li>
                    <li>Droit d'opposition au traitement</li>
                    <li>Droit de retirer votre consentement à tout moment</li>
                </ul>

                <p class="mb-4"><strong>Pour exercer ces droits, contactez-nous :</strong></p>
                <ul class="list-disc list-inside mb-4 space-y-1">
                    <li>Email : info@horizon-o.ci</li>
                    <li>Courrier : Horizon O - 28 BP 942 Abidjan 28</li>
                    <li>Téléphone : +225 01 72 93 95 95 / +225 07 47 94 42 22</li>
                </ul>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">4.7 Sécurité des données</h3>
                <p class="mb-4">Horizon O met en œuvre des mesures techniques et organisationnelles appropriées pour garantir la sécurité et la confidentialité de vos données personnelles, notamment :</p>
                <ul class="list-disc list-inside mb-4 space-y-1">
                    <li>Chiffrement des données sensibles</li>
                    <li>Accès restreint aux données</li>
                    <li>Audits de sécurité réguliers</li>
                    <li>Formation du personnel</li>
                </ul>
            </section>

            <!-- 4. Intelligence artificielle -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">5. INTELLIGENCE ARTIFICIELLE ET CONTENU GÉNÉRÉ</h2>
                
                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">5.1 Nature du service</h3>
                <p class="mb-4">
                    LagentO utilise des technologies d'intelligence artificielle avancées pour fournir des conseils et générer du contenu adapté aux besoins des entrepreneurs ivoiriens.
                </p>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">5.2 Limitations</h3>
                <ul class="list-disc list-inside mb-4 space-y-1">
                    <li>Les informations fournies sont à titre indicatif et ne remplacent pas les conseils de professionnels qualifiés</li>
                    <li>Le contenu généré peut contenir des inexactitudes malgré nos efforts d'amélioration continue</li>
                    <li>Les utilisateurs restent responsables de la vérification et de l'utilisation des informations fournies</li>
                </ul>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">5.3 Propriété du contenu généré</h3>
                <p class="mb-4">
                    Les documents et contenus générés pour l'utilisateur via LagentO deviennent la propriété de l'utilisateur, sous réserve du respect des conditions d'utilisation.
                </p>
            </section>

            <!-- 5. Responsabilité -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">6. RESPONSABILITÉ</h2>
                
                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">6.1 Limitation de responsabilité</h3>
                <p class="mb-4">Horizon O ne saurait être tenu responsable :</p>
                <ul class="list-disc list-inside mb-4 space-y-1">
                    <li>Des dommages directs ou indirects résultant de l'utilisation ou de l'impossibilité d'utiliser LagentO</li>
                    <li>Des décisions prises sur la base des informations fournies par l'IA</li>
                    <li>Des interruptions temporaires du service pour maintenance</li>
                    <li>Des pertes de données dues à des causes externes</li>
                </ul>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">6.2 Force majeure</h3>
                <p class="mb-4">
                    Horizon O ne pourra être tenu responsable en cas de force majeure ou d'événements hors de son contrôle.
                </p>
            </section>

            <!-- 6. Cookies -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">7. COOKIES ET TRACEURS</h2>
                
                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">7.1 Types de cookies utilisés</h3>
                <ul class="list-disc list-inside mb-4 space-y-1">
                    <li>Cookies essentiels : nécessaires au fonctionnement de la plateforme</li>
                    <li>Cookies de performance : pour améliorer l'expérience utilisateur</li>
                    <li>Cookies analytiques : pour comprendre l'utilisation du service</li>
                    <li>Cookies de personnalisation : pour adapter le contenu à vos besoins</li>
                </ul>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">7.2 Gestion des cookies</h3>
                <p class="mb-4">
                    Vous pouvez gérer vos préférences de cookies via les paramètres de votre compte ou de votre navigateur.
                </p>
            </section>

            <!-- 7. Conditions d'utilisation -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">8. CONDITIONS D'UTILISATION</h2>
                <p class="mb-4">
                    L'utilisation de LagentO implique l'acceptation pleine et entière des présentes mentions légales et des conditions générales d'utilisation disponibles sur la plateforme.
                </p>
            </section>

            <!-- 8. Droit applicable -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">9. DROIT APPLICABLE ET JURIDICTION</h2>
                
                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">9.1 Droit applicable</h3>
                <p class="mb-4">Les présentes mentions légales sont régies par :</p>
                <ul class="list-disc list-inside mb-4 space-y-1">
                    <li>Le droit ivoirien</li>
                    <li>Le droit OHADA applicable</li>
                    <li>Les conventions internationales ratifiées par la Côte d'Ivoire</li>
                </ul>

                <h3 class="text-base font-medium mb-3" style="color: var(--gray-900);">9.2 Règlement des litiges</h3>
                <p class="mb-4">En cas de litige :</p>
                <ol class="list-decimal list-inside mb-4 space-y-1">
                    <li>Tentative de résolution amiable obligatoire</li>
                    <li>Médiation possible auprès du Centre d'Arbitrage de la Côte d'Ivoire</li>
                    <li>Compétence exclusive des tribunaux d'Abidjan-Plateau</li>
                </ol>
            </section>

            <!-- 9. Accessibilité -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">10. ACCESSIBILITÉ</h2>
                <p class="mb-4">
                    Horizon O s'engage à rendre LagentO accessible au plus grand nombre, notamment aux personnes en situation de handicap, conformément aux standards internationaux d'accessibilité numérique.
                </p>
            </section>

            <!-- 10. Modifications -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">11. MODIFICATIONS</h2>
                <p class="mb-4">
                    Horizon O se réserve le droit de modifier les présentes mentions légales à tout moment. Les utilisateurs seront informés de toute modification substantielle.
                </p>
            </section>

            <!-- 11. Contact -->
            <section class="mb-8">
                <h2 class="text-lg font-medium mb-4" style="color: var(--gray-900);">12. CONTACT</h2>
                <p class="mb-4">Pour toute question relative aux présentes mentions légales, au service LagentO ou pour toute réclamation :</p>
                <div class="space-y-2 mb-4">
                    <p><strong>Think Tank Horizon O</strong></p>
                    <p><strong>Email :</strong> info@horizon-o.ci</p>
                    <p><strong>Téléphone :</strong> +225 01 72 93 95 95 / +225 07 47 94 42 22</p>
                    <p><strong>Adresse :</strong> 28 BP 942 Abidjan 28</p>
                    <p><strong>Site web :</strong> www.horizon-o.ci</p>
                    <p><strong>Horaires d'assistance :</strong> Lundi-Vendredi, 8h00-18h00 (GMT)</p>
                </div>
            </section>

            <!-- 12. Mise à jour -->
            <section class="mb-8">
                <div class="border-t pt-6" style="border-color: var(--gray-200);">
                    <p class="text-sm mb-2" style="color: var(--gray-500);">
                        <strong>Dernière mise à jour :</strong> 20 août 2025
                    </p>
                    <p class="text-sm mb-4" style="color: var(--gray-500);">
                        <strong>Version :</strong> 1.0
                    </p>
                    <p class="text-sm font-medium" style="color: var(--gray-700);">
                        © 2025 Think Tank Horizon O - Tous droits réservés
                    </p>
                    <p class="text-sm italic mt-2" style="color: var(--primary-600);">
                        Ensemble, construisons l'horizon entrepreneurial de la Côte d'Ivoire
                    </p>
                </div>
            </section>

        </div>
    </div>
</div>
@endsection