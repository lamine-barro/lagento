<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentAnalysisService
{
    private PdfExtractionService $pdfService;
    private string $openaiApiKey;

    public function __construct(PdfExtractionService $pdfService)
    {
        $this->pdfService = $pdfService;
        $this->openaiApiKey = env('OPENAI_API_KEY');
    }

    /**
     * Analyser un document completement avec GPT-4.1-mini
     */
    public function analyzeDocument(Document $document): array
    {
        try {
            // 1. Extraire le contenu selon le type de fichier
            $extractedContent = $this->extractContent($document);
            
            if (empty($extractedContent)) {
                throw new \Exception('Impossible d\'extraire le contenu du document');
            }

            // 2. Analyser avec GPT-4.1-mini
            $analysisResult = $this->analyzeWithGPT($extractedContent, $document->original_name);
            
            // 3. Mettre à jour le document avec les résultats
            $document->update([
                'extracted_content' => $extractedContent,
                'ai_summary' => $analysisResult['summary'],
                'detected_tags' => $analysisResult['tags'],
                'ai_metadata' => $analysisResult['metadata'],
                'is_processed' => true,
                'processed_at' => now()
            ]);

            Log::info('Document analysis completed', [
                'document_id' => $document->id,
                'filename' => $document->original_name,
                'tags_detected' => count($analysisResult['tags']),
                'content_length' => strlen($extractedContent)
            ]);

            return [
                'success' => true,
                'summary' => $analysisResult['summary'],
                'tags' => $analysisResult['tags'],
                'metadata' => $analysisResult['metadata']
            ];

        } catch (\Exception $e) {
            Log::error('Document analysis failed', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);

            $document->update([
                'processing_error' => $e->getMessage(),
                'is_processed' => false
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Extraire le contenu selon le type de fichier
     */
    private function extractContent(Document $document): string
    {
        $filePath = Storage::disk('private')->path($document->file_path);

        switch (strtolower($document->file_extension)) {
            case 'pdf':
                return $this->pdfService->extractTextFromPdf($filePath);
                
            case 'txt':
                return file_get_contents($filePath);
                
            case 'doc':
            case 'docx':
                // Pour Word, on peut utiliser une librairie comme PhpWord ou simplement le nom
                return "Document Word: " . $document->original_name . "\nContenu non extrait automatiquement.";
                
            default:
                return "Fichier: " . $document->original_name . "\nType: " . $document->mime_type;
        }
    }

    /**
     * Analyser le contenu avec GPT-4.1-mini
     */
    public function analyzeWithGPT(string $content, string $filename): array
    {
        // Limiter le contenu à 8000 caractères pour éviter les limites de tokens
        $truncatedContent = mb_substr($content, 0, 8000);

        $prompt = $this->buildAnalysisPrompt($truncatedContent, $filename);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openaiApiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini', // GPT-4.1-mini
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Tu es un expert en analyse documentaire pour entrepreneurs ivoiriens. Tu dois analyser des documents officiels et business.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 400, // Limite pour summary + métadonnées
            'temperature' => 0.3,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Erreur API OpenAI: ' . $response->body());
        }

        $aiResponse = $response->json();
        $analysisText = $aiResponse['choices'][0]['message']['content'];

        return $this->parseGPTResponse($analysisText);
    }

    /**
     * Construire le prompt d'analyse
     */
    private function buildAnalysisPrompt(string $content, string $filename): string
    {
        return "Analyse ce document entrepreneurial ivoirien et fournis UNIQUEMENT un JSON avec:

1. \"summary\": Résumé en français en 300 mots max
2. \"tags\": Array des types de documents détectés parmi: [\"RCCM\", \"DFE\", \"Statuts\", \"Pacte_actionnaires\", \"Agrément\", \"Business_plan\", \"Compte_resultat\", \"Bilan\", \"Contrat\", \"Autre\"]
3. \"metadata\": Object avec {\"document_type\": \"type principal\", \"confidence\": score 0-1, \"key_info\": \"infos clés extraites\"}

Document: {$filename}
Contenu:
{$content}

RÉPONSE (JSON uniquement):";
    }

    /**
     * Parser la réponse GPT en structure
     */
    private function parseGPTResponse(string $response): array
    {
        try {
            // Nettoyer la réponse (enlever markdown, etc.)
            $cleanResponse = preg_replace('/```json\s*|\s*```/', '', trim($response));
            $data = json_decode($cleanResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON from GPT: ' . json_last_error_msg());
            }

            return [
                'summary' => $data['summary'] ?? 'Résumé non disponible',
                'tags' => $data['tags'] ?? [],
                'metadata' => $data['metadata'] ?? []
            ];

        } catch (\Exception $e) {
            Log::warning('Failed to parse GPT response', [
                'response' => $response,
                'error' => $e->getMessage()
            ]);

            // Fallback
            return [
                'summary' => 'Analyse automatique non disponible. Document téléchargé avec succès.',
                'tags' => ['Autre'],
                'metadata' => [
                    'document_type' => 'Indéterminé',
                    'confidence' => 0,
                    'key_info' => 'Analyse manuelle requise'
                ]
            ];
        }
    }

    /**
     * Traiter tous les documents en attente d'un utilisateur
     */
    public function processPendingDocuments(string $userId): int
    {
        $pendingDocuments = Document::where('user_id', $userId)
            ->where('is_processed', false)
            ->get();

        $processed = 0;

        foreach ($pendingDocuments as $document) {
            $result = $this->analyzeDocument($document);
            if ($result['success']) {
                $processed++;
            }
            
            // Pause pour éviter rate limiting
            sleep(1);
        }

        return $processed;
    }

    /**
     * Obtenir les statistiques de tags pour un utilisateur
     */
    public function getUserDocumentStats(string $userId): array
    {
        $documents = Document::where('user_id', $userId)
            ->where('is_processed', true)
            ->get();

        $tagStats = [];
        foreach ($documents as $document) {
            $tags = $document->detected_tags ?? [];
            foreach ($tags as $tag) {
                $tagStats[$tag] = ($tagStats[$tag] ?? 0) + 1;
            }
        }

        return [
            'total_documents' => $documents->count(),
            'tag_distribution' => $tagStats,
            'most_common_tags' => array_keys(array_slice(arsort($tagStats) ? $tagStats : [], 0, 5, true))
        ];
    }
}