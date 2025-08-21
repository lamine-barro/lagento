<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentAnalysisService
{
    private PdfExtractionService $pdfService;
    protected LanguageModelService $languageModel;

    public function __construct(PdfExtractionService $pdfService, LanguageModelService $languageModel)
    {
        $this->pdfService = $pdfService;
        $this->languageModel = $languageModel;
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
                
            case 'jpeg':
            case 'jpg':
            case 'png':
            case 'gif':
            case 'bmp':
            case 'webp':
                return $this->extractContentFromImage($document);
                
            default:
                return "Fichier: " . $document->original_name . "\nType: " . $document->mime_type;
        }
    }

    /**
     * Extraire le contenu d'une image avec GPT-5-mini Vision
     */
    private function extractContentFromImage(Document $document): string
    {
        try {
            $filePath = Storage::disk('private')->path($document->file_path);
            
            // Encoder l'image en base64
            $imageData = file_get_contents($filePath);
            $base64Image = base64_encode($imageData);
            $mimeType = $document->mime_type ?: 'image/jpeg';
            
            $messages = [
                [
                    'role' => 'system',
                    'content' => 'Tu es un expert OCR et analyste de documents entrepreneuriaux. Ta mission est d\'extraire TOUT le texte visible dans cette image, même si l\'image est de mauvaise qualité. Utilise tes capacités de vision avancées pour déchiffrer le texte flou, en biais ou mal éclairé.'
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'IMPORTANT: Même si l\'image est floue ou de mauvaise qualité, essaie d\'extraire TOUT le texte visible. Fournis:\n1. Tout le texte que tu peux lire (même partiellement)\n2. Le type de document identifié\n3. Les informations clés (noms, dates, montants, etc.)\n4. Si vraiment aucun texte n\'est lisible, décris ce que tu vois visuellement'
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:{$mimeType};base64,{$base64Image}",
                                'detail' => 'high'
                            ]
                        ]
                    ]
                ]
            ];

            $extractedText = $this->languageModel->chat(
                messages: $messages,
                model: 'gpt-4.1-mini',
                temperature: 0.1,
                maxTokens: 1500
            );

            return $extractedText ?: "Impossible d'extraire le contenu de l'image.";
            
        } catch (\Exception $e) {
            Log::error('Image content extraction failed', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            return "Erreur lors de l'extraction du contenu de l'image: " . $e->getMessage();
        }
    }

    /**
     * Analyser le contenu avec GPT-4.1-mini
     */
    public function analyzeWithGPT(string $content, string $filename): array
    {
        try {
            // Limiter le contenu à 8000 caractères pour éviter les limites de tokens
            $truncatedContent = mb_substr($content, 0, 8000);

            $prompt = $this->buildAnalysisPrompt($truncatedContent, $filename);

            $messages = [
                [
                    'role' => 'system',
                    'content' => 'Tu es un expert en analyse documentaire pour entrepreneurs. Tu dois analyser des documents officiels et business et retourner UNIQUEMENT du JSON valide.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ];

            $analysisText = $this->languageModel->chat(
                messages: $messages,
                model: 'gpt-4.1-mini',
                temperature: 0.2,
                maxTokens: 1000,
                options: [
                    'response_format' => ['type' => 'json_object']
                ]
            );

            // Log pour debug
            Log::info('GPT analysis response received', [
                'response_length' => strlen($analysisText),
                'response_preview' => substr($analysisText, 0, 200)
            ]);

            return $this->parseGPTResponse($analysisText);
        } catch (\Exception $e) {
            Log::error('Document analysis with LLM failed', [
                'error' => $e->getMessage(),
                'filename' => $filename
            ]);
            throw new \Exception('Erreur analyse LLM: ' . $e->getMessage());
        }
    }

    /**
     * Construire le prompt d'analyse
     */
    private function buildAnalysisPrompt(string $content, string $filename): string
    {
        return "Analyse ce document entrepreneurial et fournis UNIQUEMENT un JSON avec:

1. \"summary\": Résumé en français en 300 mots max
2. \"tags\": Array des types de documents détectés parmi: [\"RCCM\", \"DFE\", \"Statuts\", \"Pacte_actionnaires\", \"Agrément\", \"Business_plan\", \"Compte_resultat\", \"Bilan\", \"Contrat\", \"Facture\", \"Devis\", \"Attestation\", \"Certificat\", \"Rapport\", \"Autre\"]
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
            // Check if response is empty
            if (empty(trim($response))) {
                Log::warning('Empty GPT response, using fallback');
                throw new \Exception('Empty response from GPT');
            }
            
            Log::debug('Parsing GPT response', ['raw_response' => $response]);

            // Nettoyer la réponse (enlever markdown, etc.)
            $cleanResponse = preg_replace('/```json\s*|\s*```/', '', trim($response));
            $cleanResponse = preg_replace('/```\s*|\s*```/', '', $cleanResponse);
            
            // Remove any text before the first { and after the last }
            if (preg_match('/\{.*\}/s', $cleanResponse, $matches)) {
                $cleanResponse = $matches[0];
            }
            
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
                'summary' => 'Document téléchargé avec succès. Pour une analyse optimale, assurez-vous que l\'image soit nette, bien éclairée et que le texte soit lisible. Vous pouvez également essayer de re-télécharger le document en meilleure qualité.',
                'tags' => ['Autre'],
                'metadata' => [
                    'document_type' => 'Analyse en attente',
                    'confidence' => 0.1,
                    'key_info' => 'Qualité d\'image insuffisante pour l\'extraction automatique. Recommandation : re-télécharger en haute qualité.'
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