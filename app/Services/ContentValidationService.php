<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ContentValidationService
{
    protected LanguageModelService $languageModel;
    
    public function __construct(LanguageModelService $languageModel)
    {
        $this->languageModel = $languageModel;
    }

    /**
     * Valide le contenu d'une étape d'onboarding pour détecter les faux contenus
     */
    public function validateOnboardingStep(array $data, string $step): array
    {
        try {
            switch ($step) {
                case 'step1':
                    return $this->validateStep1($data);
                case 'step2':
                    return $this->validateStep2($data);
                case 'step3':
                    return $this->validateStep3($data);
                case 'step4':
                    return $this->validateStep4($data);
                default:
                    return ['valid' => true, 'reason' => null];
            }
        } catch (\Exception $e) {
            Log::error("Erreur validation LLM pour {$step}: " . $e->getMessage());
            // En cas d'erreur, on laisse passer pour ne pas bloquer l'utilisateur
            return ['valid' => true, 'reason' => null];
        }
    }

    /**
     * Validation Step 1: Informations générales du projet
     */
    private function validateStep1(array $data): array
    {
        $content = [
            'nom_projet' => $data['nom_projet'] ?? '',
            'raison_sociale' => $data['raison_sociale'] ?? '',
            'description' => $data['description'] ?? ''
        ];

        $prompt = "Tu es un expert en validation de données d'entreprises. Analyse les informations suivantes et détermine UNIQUEMENT s'il s'agit de contenus MANIFESTEMENT FAUX ou de spam évident.

DONNÉES À ANALYSER:
- Nom du projet: {$content['nom_projet']}
- Raison sociale: {$content['raison_sociale']}
- Description: {$content['description']}

CRITÈRES STRICTS - BLOQUER SEULEMENT SI:
- Noms MANIFESTEMENT faux (ex: 'Test Company', 'Fake Business', 'Lorem Ipsum SA', 'AZERTY Corp')
- Descriptions TOTALEMENT vides ou absurdes (ex: 'test test test', 'blabla', caractères aléatoires)
- Texte répétitif évident ou spam flagrant

ACCEPTER TOUJOURS:
- Descriptions courtes mais réelles (ex: 'Spécialistes en data et IA')
- Noms d'entreprises existants ou plausibles (ex: 'TechCorp', etc.)
- Contenus simples mais authentiques

Ne rejette QUE les contenus ÉVIDEMMENT faux ou spam.

Réponds UNIQUEMENT par un JSON avec cette structure exacte:
{
  \"valid\": true/false,
  \"confidence\": 0.0-1.0,
  \"reason\": \"explication courte si invalid\"
}";

        return $this->callOpenAI($prompt);
    }

    /**
     * Validation Step 2: Informations de contact et localisation
     */
    private function validateStep2(array $data): array
    {
        $content = [
            'telephone' => $data['telephone'] ?? '',
            'email' => $data['email'] ?? '',
            'site_web' => $data['site_web'] ?? '',
            'reseaux_sociaux' => $data['reseaux_sociaux'] ?? []
        ];

        $prompt = "Analyse ces informations de contact UNIQUEMENT pour détecter du spam évident ou des données MANIFESTEMENT fausses:

DONNÉES:
- Téléphone: {$content['telephone']}
- Email: {$content['email']}
- Site web: {$content['site_web']}
- Réseaux sociaux: " . json_encode($content['reseaux_sociaux']) . "

BLOQUER SEULEMENT SI:
- Numéros de téléphone absurdes (ex: '123456789', '000000000')
- Emails ÉVIDEMMENT fake (ex: 'fake@fake.com', 'test@test.test')
- URLs manifestement factices (ex: 'www.fake.fake')

ACCEPTER TOUJOURS:
- Emails normaux même temporaires
- Numéros de téléphone plausibles
- Sites web réels ou vides
- Profils réseaux sociaux normaux

Réponds par un JSON:
{
  \"valid\": true/false,
  \"confidence\": 0.0-1.0,
  \"reason\": \"explication si invalid\"
}";

        return $this->callOpenAI($prompt);
    }

    /**
     * Validation Step 3: Secteurs et modèles d'affaires
     */
    private function validateStep3(array $data): array
    {
        $content = [
            'secteurs' => $data['secteurs'] ?? [],
            'produits_services' => $data['produits_services'] ?? '',
            'cibles' => $data['cibles'] ?? [],
            'maturite' => $data['maturite'] ?? '',
            'modeles_revenus' => $data['modeles_revenus'] ?? []
        ];

        $prompt = "Vérifie ces données business UNIQUEMENT pour détecter du spam évident:

DONNÉES:
- Secteurs: " . json_encode($content['secteurs']) . "
- Produits/Services: {$content['produits_services']}
- Cibles: " . json_encode($content['cibles']) . "
- Maturité: {$content['maturite']}
- Modèles de revenus: " . json_encode($content['modeles_revenus']) . "

BLOQUER SEULEMENT SI:
- Descriptions TOTALEMENT absurdes (ex: 'blabla', 'test test')
- Contenus manifestement répétitifs ou spam

ACCEPTER TOUJOURS:
- Descriptions courtes mais réelles
- Combinaisons de secteurs diverses
- Cibles larges ou spécifiques
- Toute donnée plausible même simple

Réponds par un JSON:
{
  \"valid\": true/false,
  \"confidence\": 0.0-1.0,
  \"reason\": \"explication si invalid\"
}";

        return $this->callOpenAI($prompt);
    }

    /**
     * Validation Step 4: Équipe et besoins
     */
    private function validateStep4(array $data): array
    {
        $content = [
            'nombre_fondateurs' => $data['nombre_fondateurs'] ?? 0,
            'nombre_fondatrices' => $data['nombre_fondatrices'] ?? 0,
            'tranches_age_fondateurs' => $data['age_ranges'] ?? [],
            'localisation_fondateurs' => $data['founders_location'] ?? '',
            'taille_equipe' => $data['team_size'] ?? '',
            'structures_accompagnement' => $data['support_structures'] ?? [],
            'types_soutien' => $data['support_types'] ?? [],
            'details_besoins' => $data['details_besoins'] ?? ''
        ];

        $prompt = "Analyse ces informations d'équipe UNIQUEMENT pour détecter du spam évident:

DONNÉES:
- Fondateurs: {$content['nombre_fondateurs']}
- Fondatrices: {$content['nombre_fondatrices']}
- Tranches d'âge: " . json_encode($content['tranches_age_fondateurs']) . "
- Localisation: {$content['localisation_fondateurs']}
- Taille équipe: {$content['taille_equipe']}
- Structures: " . json_encode($content['structures_accompagnement']) . "
- Types soutien: " . json_encode($content['types_soutien']) . "
- Détails besoins: {$content['details_besoins']}

BLOQUER SEULEMENT SI:
- Nombres absurdes (ex: 999 fondateurs)
- Contenus TOTALEMENT absurdes ou spam évident
- Texte répétitif manifeste

ACCEPTER TOUJOURS:
- Équipes de toute taille normale
- Besoins exprimés simplement
- Combinaisons diverses mais plausibles
- Descriptions courtes mais réelles

Réponds par un JSON:
{
  \"valid\": true/false,
  \"confidence\": 0.0-1.0,
  \"reason\": \"explication si invalid\"
}";

        return $this->callOpenAI($prompt);
    }

    /**
     * Appel à l'API OpenAI via LanguageModelService
     */
    private function callOpenAI(string $prompt): array
    {
        try {
            $messages = [
                [
                    'role' => 'system',
                    'content' => 'Tu es un expert en validation de données d\'entreprises. Réponds UNIQUEMENT par du JSON valide, sans texte supplémentaire.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ];

            $content = $this->languageModel->chat(
                messages: $messages,
                model: 'gpt-4.1-mini',
                maxTokens: 150,
                options: [
                    'response_format' => ['type' => 'json_object']
                ]
            );
            
            // Parser le JSON retourné
            $validation = json_decode($content, true);
            
            if (!$validation || !isset($validation['valid'])) {
                Log::error('Réponse OpenAI invalide: ' . $content);
                throw new \Exception('Réponse OpenAI invalide');
            }

            return [
                'valid' => (bool) $validation['valid'],
                'confidence' => (float) ($validation['confidence'] ?? 0.5),
                'reason' => $validation['reason'] ?? null
            ];
        } catch (\Exception $e) {
            Log::error('Erreur validation LLM: ' . $e->getMessage());
            // En cas d'erreur de l'API, on laisse passer pour ne pas bloquer l'utilisateur
            return [
                'valid' => true,
                'confidence' => 0.0,
                'reason' => 'Validation impossible (erreur API) - autorisé par défaut'
            ];
        }
    }
}