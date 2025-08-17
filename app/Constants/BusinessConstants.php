<?php

namespace App\Constants;

class BusinessConstants
{
    public const REGIONS = [
        'Abidjan' => ['lat' => 5.345, 'lng' => -4.024],
        'Yamoussoukro' => ['lat' => 6.820, 'lng' => -5.280],
        'Agnéby‑Tiassa' => ['lat' => 6.000, 'lng' => -4.000],
        'Bafing' => ['lat' => 8.284, 'lng' => -7.683],
        'Bagoué' => ['lat' => 9.520, 'lng' => -6.490],
        'Bélier' => ['lat' => 6.552, 'lng' => -5.000],
        'Béré' => ['lat' => 8.550, 'lng' => -6.190],
        'Bounkani' => ['lat' => 9.270, 'lng' => -3.310],
        'Cavally' => ['lat' => 6.540, 'lng' => -7.490],
        'Folon' => ['lat' => 9.830, 'lng' => -7.570],
        'Gbêkê' => ['lat' => 7.680, 'lng' => -5.030],
        'Gbôklé' => ['lat' => 5.900, 'lng' => -6.070],
        'Gôh' => ['lat' => 6.130, 'lng' => -5.940],
        'Gontougo' => ['lat' => 8.040, 'lng' => -2.789],
        'Grands‑Ponts' => ['lat' => 5.320, 'lng' => -4.380],
        'Guémon' => ['lat' => 6.730, 'lng' => -7.370],
        'Hambol' => ['lat' => 8.140, 'lng' => -5.100],
        'Haut‑Sassandra' => ['lat' => 6.890, 'lng' => -6.450],
        'Iffou' => ['lat' => 7.050, 'lng' => -3.980],
        'Indénié‑Djuablin' => ['lat' => 6.730, 'lng' => -3.500],
        'Kabadougou' => ['lat' => 9.510, 'lng' => -7.560],
        'La Mé' => ['lat' => 6.090, 'lng' => -3.870],
        'LôhDjiboua' => ['lat' => 5.830, 'lng' => -5.360],
        'Marahoué' => ['lat' => 6.990, 'lng' => -5.750],
        'Moronou' => ['lat' => 6.650, 'lng' => -4.750],
        'Nawa' => ['lat' => 5.780, 'lng' => -6.610],
        'Nzi' => ['lat' => 6.650, 'lng' => -4.700],
        'Poro' => ['lat' => 9.460, 'lng' => -5.630],
        'San‑Pédro' => ['lat' => 4.770, 'lng' => -6.640],
        'Sud‑Comoé' => ['lat' => 5.470, 'lng' => -3.200],
        'Tchologo' => ['lat' => 9.600, 'lng' => -5.200],
        'Tonkpi' => ['lat' => 7.390, 'lng' => -7.550],
        'Worodougou' => ['lat' => 7.960, 'lng' => -6.680],
    ];

    public const TYPES_OPPORTUNITES = [
        'ASSISTANCE_TECHNIQUE' => 'Assistance technique',
        'FORMATION' => 'Formation',
        'FINANCEMENT' => 'Financement',
        'APPEL_OFFRES' => 'Appel d\'offres',
        'INCUBATION' => 'Incubation',
        'ACCELERATION' => 'Accélération',
        'STUDIO' => 'Studio',
        'CONCOURS' => 'Concours',
        'EVENEMENT' => 'Événement',
        'PROMOTION' => 'Promotion'
    ];

    public const SECTEURS = [
        'AGRICULTURE' => 'Agriculture & Agro-alimentaire',
        'RESSOURCES' => 'Ressources & Mines',
        'ENERGIE' => 'Énergie & Climat',
        'ENVIRONNEMENT' => 'Eau, Déchets & Recyclage',
        'INDUSTRIE' => 'Industrie & Fabrication',
        'CONSTRUCTION' => 'Construction & Immobilier',
        'TRANSPORT' => 'Transport & Mobilité',
        'COMMERCE' => 'Commerce & Distribution',
        'FINANCE' => 'Services financiers & Assurance',
        'NUMERIQUE' => 'Télécoms & Services numériques',
        'MEDIAS' => 'Médias, Culture & Divertissement',
        'TOURISME' => 'Tourisme & Hospitalité',
        'SANTE' => 'Santé & Bien-être',
        'EDUCATION' => 'Éducation & Formation',
        'SERVICES_PRO' => 'Services professionnels',
        'RECHERCHE' => 'Recherche & Innovation',
        'PUBLIC' => 'Administration & Services publics',
        'SECURITE' => 'Sécurité & Défense',
        'IMPACT_SOCIAL' => 'Impact social & ONG',
        'SERVICES_PERSO' => 'Services personnels & Domestiques'
    ];

    public const STADES_MATURITE = [
        'IDEE' => 'Idée',
        'PROTOTYPE' => 'Prototype',
        'LANCEMENT' => 'Lancement',
        'CROISSANCE' => 'Croissance',
        'EXPANSION' => 'Expansion'
    ];

    public const CIBLES = [
        'B2C' => 'Particuliers',
        'B2B' => 'Entreprises',
        'B2G' => 'Gouvernement',
    ];

    public const STADES_FINANCEMENT = [
        'AUCUN' => 'Aucun financement',
        'SUBVENTION' => 'Subvention',
        'PRE_AMORCAGE' => 'Pré-amorçage',
        'AMORCAGE' => 'Amorçage',
        'CROISSANCE' => 'Croissance',
        'EXPANSION' => 'Expansion',
        'IPO' => 'Introduction en bourse',
        'ACQUIS' => 'Acquis',
        'NON_APPLICABLE' => 'Non applicable'
    ];

    public const MODELES_REVENUS = [
        'ABONNEMENT_SAAS' => 'Abonnement / SaaS',
        'PAIEMENT_USAGE' => 'Paiement à l\'usage',
        'COMMISSION_MARKETPLACE' => 'Commission & Marketplace',
        'PUBLICITE_SPONSORING' => 'Publicité & Sponsoring',
        'LICENCE_REDEVANCES' => 'Licence & Redevances',
        'VENTE_DIRECTE' => 'Vente directe',
        'PRESTATIONS_SERVICE' => 'Prestations & Conseil',
        'AFFILIATION' => 'Affiliation',
        'DONS_FINANCEMENT' => 'Dons & Financement participatif',
        'AUTRE' => 'Autre'
    ];

    public const TRANCHES_REVENUS = [
        'ZERO' => 'Pas de revenu',
        'MOINS_5M' => 'Moins de 5 millions FCFA',
        'DE_5_A_50M' => '5 à 50 millions FCFA',
        'DE_50_A_100M' => '50 à 100 millions FCFA',
        'DE_100_A_500M' => '100 à 500 millions FCFA',
        'DE_500M_A_1MD' => '500 millions à 1 milliard FCFA',
        'DE_1_A_10MD' => '1 à 10 milliards FCFA',
        'PLUS_10MD' => 'Plus de 10 milliards FCFA'
    ];

    public const AGE_RANGES = [
        '18-25',
        '26-35', 
        '36-45',
        '46-55',
        '55+'
    ];

    public const TEAM_SIZES = [
        '1-5',
        '6-10',
        '11-20',
        '21-50',
        '50+'
    ];

    public const TYPES_SOUTIEN = [
        'FINANCEMENT' => 'Accès au financement',
        'MARCHE' => 'Accès au marché/clients',
        'JURIDIQUE' => 'Accompagnement juridique',
        'MENTORAT' => 'Coaching/Mentorat',
        'ESPACES' => 'Espaces de travail',
        'FORMATION' => 'Formation',
        'PARTENAIRES' => 'Mise en relation',
        'RH' => 'Recrutement/RH',
        'VISIBILITE' => 'Visibilité médiatique',
        'AUTRE' => 'Autre'
    ];

    public const STRUCTURES_ACCOMPAGNEMENT = [
        'ABX Accelerator',
        'ADEI Tech Hub',
        'Akendewa Tech Hub',
        'Autre',
        'CGECI Accélérateur',
        'ESPartners',
        'Fondation Jeunesse Numérique',
        'French Tech Abidjan',
        'HEC Challenge Plus',
        'H‑FABLAB',
        'Hub Ivoire Tech',
        'IECD',
        'Impact Hub Abidjan',
        'Incub\'Ivoir',
        'INOVIS',
        'Jokkolabs Abidjan',
        'MPME CI',
        'Orange Fab CI',
        'Seedstars / Seedspace Abidjan',
        'Ubuntu Capital',
        'Y\'ello Startup (MTN)',
        'Yiri',
        'ZEBOX West Africa'
    ];

    public const CATEGORIES_INSTITUTIONS = [
        // Création & Formalisation (Priorité 1)
        'GUICHET_UNIQUE' => 'Guichet Unique (CEPICI, Création d\'entreprise)',
        'TRIBUNAL_COMMERCE' => 'Tribunal de Commerce',
        'NOTAIRE' => 'Notaire (Statuts, Actes)',
        
        // Financement (Priorité 1)
        'FONDS_INVESTISSEMENT' => 'Fonds d\'Investissement & Capital-risque',
        'BANQUE_DEVELOPPEMENT' => 'Banques & Institutions de Financement',
        'MICROFINANCE' => 'Microfinance & Financement Alternatif',
        
        // Accompagnement & Accélération (Priorité 1)
        'INCUBATEUR_ACCELERATEUR' => 'Incubateurs & Accélérateurs',
        'HUB_INNOVATION' => 'Hubs d\'Innovation & Tech',
        'ESPACE_COWORKING' => 'Espaces de Coworking & FabLabs',
        
        // Appui Institutionnel
        'MINISTERE_AGENCE' => 'Ministères & Agences Gouvernementales',
        'CHAMBRE_COMMERCE' => 'Chambre de Commerce et d\'Industrie',
        'AGENCE_PROMOTION' => 'Agences de Promotion (Export, Investissement)',
        
        // Formation & Réseautage
        'CENTRE_FORMATION' => 'Centres de Formation Entrepreneuriale',
        'ASSOCIATION_ENTREPRENEURIALE' => 'Associations & Réseaux d\'Entrepreneurs',
        
        // Services Professionnels
        'CABINET_CONSEIL' => 'Cabinets de Conseil & Expertise',
        'CABINET_JURIDIQUE' => 'Avocats & Conseil Juridique',
        
        // Régulation & Conformité
        'AUTORITE_REGULATION' => 'Autorités de Régulation Sectorielles',
        'FISCALITE' => 'Administration Fiscale (DGI, Douanes)',
        
        // International & Diaspora
        'ORGANISATION_INTERNATIONALE' => 'Bailleurs & Organisations Internationales',
        'COOPERATION_DIASPORA' => 'Programmes Diaspora & Coopération'
    ];

    public const CATEGORIES_TEXTES_OFFICIELS = [
        'Constitutions',
        'Lois organiques', 
        'Lois ordinaires',
        'Codes',
        'Ordonnances',
        'Décrets',
        'Arrêtés ministériels',
        'Arrêtés préfectoraux',
        'Décisions de justice',
        'Jurisprudence',
        'Décisions de régulateurs',
        'Actes uniformes OHADA',
        'Règlements UEMOA/CEDEAO',
        'Traités internationaux',
        'Circulaires',
        'Instructions',
        'Bulletins officiels',
        'Journaux officiels',
        'Coutumes codifiées'
    ];

    public const CLASSIFICATIONS_JURIDIQUES = [
        'Droit constitutionnel',
        'Droit administratif',
        'Droit fiscal et douanier',
        'Droit commercial général',
        'Droit OHADA',
        'Droit bancaire et financier',
        'Droit des sociétés',
        'Droit pénal des affaires',
        'Droit des obligations et contrats',
        'Droit foncier et domanial',
        'Droit du travail',
        'Droit de la sécurité sociale',
        'Droit des assurances',
        'Droit maritime et portuaire',
        'Droit des transports',
        'Droit minier et pétrolier',
        'Droit de l\'environnement',
        'Droit des télécommunications',
        'Droit de la famille',
        'Droit international privé',
        'Droit communautaire ouest-africain'
    ];

    public const STATUTS_DOCUMENTS = [
        'Projet',
        'En vigueur',
        'Modifié',
        'Suspendu',
        'Abrogé',
        'Archivé'
    ];

    public const CONVERSATION_STATUS = [
        'active',
        'archivée',
        'en_attente'
    ];

    public const MESSAGE_ROLES = [
        'user',
        'assistant'
    ];

    public const ATTACHMENT_TYPES = [
        'image',
        'document',
        'audio'
    ];

    public const PROFILE_TYPES = [
        'entrepreneur',
        'investisseur',
        'consultant',
        'institution'
    ];

    public const VERIFICATION_STATUS = [
        'unverified',
        'pending',
        'verified',
        'rejected'
    ];

    // Constantes pour le modèle Projet
    public const FORMALISE_OPTIONS = [
        'OUI' => 'Formalisé',
        'NON' => 'Non formalisé'
    ];

    public const LOCALISATION_FONDATEURS = [
        'LOCAL' => 'Résidents locaux',
        'DIASPORA' => 'Diaspora ivoirienne',
        'MIXTE' => 'Équipe mixte'
    ];

    public const RESEAUX_SOCIAUX = [
        'INSTAGRAM' => 'Instagram',
        'YOUTUBE' => 'YouTube', 
        'X' => 'X (Twitter)',
        'TIKTOK' => 'TikTok',
        'LINKEDIN' => 'LinkedIn',
        'FACEBOOK' => 'Facebook',
        'WHATSAPP_BUSINESS' => 'WhatsApp Business'
    ];

    public const NOMBRE_FONDATEURS_OPTIONS = [
        '1' => '1 fondateur',
        '2' => '2 fondateurs',
        '3' => '3 fondateurs',
        '4' => '4 fondateurs',
        '5+' => '5 fondateurs ou plus'
    ];

    public const NOMBRE_FONDATRICES_OPTIONS = [
        '0' => 'Aucune fondatrice',
        '1' => '1 fondatrice',
        '2' => '2 fondatrices',
        '3' => '3 fondatrices',
        '4' => '4 fondatrices',
        '5+' => '5 fondatrices ou plus'
    ];

    public const ANNEES_CREATION = [
        '2024' => '2024',
        '2023' => '2023',
        '2022' => '2022',
        '2021' => '2021',
        '2020' => '2020',
        '2019' => '2019',
        '2018' => '2018',
        '2017' => '2017',
        '2016' => '2016',
        '2015' => '2015',
        'AVANT_2015' => 'Avant 2015'
    ];
}