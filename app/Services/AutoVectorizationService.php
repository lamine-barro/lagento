<?php

namespace App\Services;

use App\Models\UserAnalytics;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AutoVectorizationService
{
    protected OpenAIVectorService $vectorService;

    public function __construct(OpenAIVectorService $vectorService)
    {
        $this->vectorService = $vectorService;
    }

    /**
     * Vectorize user diagnostic automatically
     */
    public function vectorizeDiagnostic(UserAnalytics $diagnostic): bool
    {
        try {
            $user = User::find($diagnostic->user_id);
            if (!$user) {
                Log::warning('User not found for diagnostic vectorization', ['diagnostic_id' => $diagnostic->id]);
                return false;
            }

            // Prepare diagnostic content
            $profile = $diagnostic->entrepreneur_profile ?? [];
            
            $content = $this->formatDiagnosticContent($user, $diagnostic, $profile);
            
            $vectorId = "user_diagnostic_{$diagnostic->id}";
            $namespace = "user_diagnostics";
            
            $metadata = [
                'type' => 'user_diagnostic',
                'user_id' => $diagnostic->user_id,
                'diagnostic_id' => $diagnostic->id,
                'generated_at' => $diagnostic->generated_at?->toISOString() ?? '',
                'niveau_global' => $profile['niveau_global'] ?? 'non_defini',
                'profil_type' => $profile['profil_type'] ?? 'non_defini',
                'score_potentiel' => $profile['score_potentiel'] ?? 0,
                'user_name' => $user->name ?? '',
                'user_email' => $user->email ?? ''
            ];

            $success = $this->vectorService->processAndStore(
                content: $content,
                vectorId: $vectorId,
                metadata: $metadata,
                namespace: $namespace,
                maxChunkSize: 800
            );

            if ($success) {
                Log::info('Diagnostic vectorized successfully', [
                    'diagnostic_id' => $diagnostic->id,
                    'user_id' => $diagnostic->user_id
                ]);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('Failed to vectorize diagnostic', [
                'diagnostic_id' => $diagnostic->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Vectorize conversation summary automatically (every 10 messages)
     */
    public function vectorizeConversationSummary(Conversation $conversation): bool
    {
        try {
            $messages = $conversation->messages()
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            if ($messages->count() < 10) {
                return false; // Wait for more messages
            }

            // Create conversation summary
            $content = $this->formatConversationSummary($conversation, $messages);
            
            $vectorId = "conversation_summary_{$conversation->id}_" . now()->format('Y_m_d_H_i_s');
            $namespace = "conversation_summaries";
            
            $metadata = [
                'type' => 'conversation_summary',
                'conversation_id' => $conversation->id,
                'user_id' => $conversation->user_id,
                'messages_count' => $messages->count(),
                'created_at' => now()->toISOString(),
                'conversation_title' => $conversation->title,
                'conversation_created' => $conversation->created_at->toISOString()
            ];

            $success = $this->vectorService->processAndStore(
                content: $content,
                vectorId: $vectorId,
                metadata: $metadata,
                namespace: $namespace,
                maxChunkSize: 1000
            );

            if ($success) {
                Log::info('Conversation summary vectorized', [
                    'conversation_id' => $conversation->id,
                    'messages_included' => $messages->count()
                ]);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('Failed to vectorize conversation summary', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Vectorize message attachment content
     */
    public function vectorizeAttachment(string $filePath, array $metadata = []): bool
    {
        try {
            // Extract content based on file type
            $content = $this->extractAttachmentContent($filePath, $metadata);
            
            if (empty($content)) {
                Log::warning('No content extracted from attachment', ['file_path' => $filePath]);
                return false;
            }

            $fileName = basename($filePath);
            $vectorId = "attachment_" . md5($filePath . time());
            $namespace = "message_attachments";
            
            $attachmentMetadata = array_merge($metadata, [
                'type' => 'message_attachment',
                'file_name' => $fileName,
                'file_path' => $filePath,
                'extracted_at' => now()->toISOString(),
                'content_length' => strlen($content)
            ]);

            $success = $this->vectorService->processAndStore(
                content: $content,
                vectorId: $vectorId,
                metadata: $attachmentMetadata,
                namespace: $namespace,
                maxChunkSize: 1200
            );

            if ($success) {
                Log::info('Attachment vectorized successfully', [
                    'file_name' => $fileName,
                    'content_length' => strlen($content)
                ]);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('Failed to vectorize attachment', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Perform RAG search for attachment context
     */
    public function searchAttachmentContext(string $query, string $userId = null): array
    {
        $filter = ['type' => 'message_attachment'];
        
        if ($userId) {
            $filter['user_id'] = $userId;
        }

        return $this->vectorService->searchSimilar(
            query: $query,
            topK: 5,
            filter: $filter,
            namespace: 'message_attachments'
        );
    }

    /**
     * Format diagnostic content for vectorization
     */
    private function formatDiagnosticContent(User $user, UserAnalytics $diagnostic, array $profile): string
    {
        $forces = [];
        $axes = [];
        $besoins = [];

        // Extract forces
        if (isset($profile['forces']) && is_array($profile['forces'])) {
            foreach ($profile['forces'] as $force) {
                if (is_array($force) && isset($force['domaine'])) {
                    $forces[] = $force['domaine'];
                } elseif (is_string($force)) {
                    $forces[] = $force;
                }
            }
        }

        // Extract axes progression
        if (isset($profile['axes_progression']) && is_array($profile['axes_progression'])) {
            foreach ($profile['axes_progression'] as $axe) {
                if (is_array($axe) && isset($axe['domaine'])) {
                    $axes[] = $axe['domaine'];
                } elseif (is_string($axe)) {
                    $axes[] = $axe;
                }
            }
        }

        // Extract besoins formation
        if (isset($profile['besoins_formation']) && is_array($profile['besoins_formation'])) {
            foreach ($profile['besoins_formation'] as $besoin) {
                if (is_string($besoin)) {
                    $besoins[] = $besoin;
                }
            }
        }

        return implode("\n\n", array_filter([
            "DIAGNOSTIC ENTREPRENEURIAL - {$user->name}",
            "Email: {$user->email}",
            "Date génération: " . ($diagnostic->generated_at ? $diagnostic->generated_at->format('Y-m-d H:i:s') : 'Non définie'),
            "",
            "PROFIL ENTREPRENEURIAL:",
            "Niveau global: " . ($profile['niveau_global'] ?? 'Non défini'),
            "Score potentiel: " . ($profile['score_potentiel'] ?? 'Non défini'),
            "Type de profil: " . ($profile['profil_type'] ?? 'Non défini'),
            "",
            "FORCES IDENTIFIÉES:",
            empty($forces) ? 'Aucune force identifiée' : implode(', ', $forces),
            "",
            "AXES D'AMÉLIORATION:",
            empty($axes) ? 'Aucun axe identifié' : implode(', ', $axes),
            "",
            "BESOINS DE FORMATION:",
            empty($besoins) ? 'Aucun besoin identifié' : implode(', ', $besoins),
            "",
            "MÉTRIQUES:",
            "Score santé: " . ($diagnostic->score_sante ?? 'Non défini'),
            "Viabilité: " . ($diagnostic->viabilite ?? 'Non définie'),
            "Position marché: " . ($diagnostic->position_marche ?? 'Non définie'),
            "Nombre opportunités: " . ($diagnostic->nombre_opportunites ?? 0),
            "",
            "MESSAGE PRINCIPAL:",
            $diagnostic->message_principal ?? 'Aucun message principal défini'
        ]));
    }

    /**
     * Format conversation summary for vectorization
     */
    private function formatConversationSummary(Conversation $conversation, $messages): string
    {
        $user = User::find($conversation->user_id);
        $messageTexts = [];

        foreach ($messages->reverse() as $message) {
            $role = $message->role === 'user' ? 'UTILISATEUR' : 'ASSISTANT';
            $content = substr($message->content, 0, 500); // Limit message length
            $messageTexts[] = "{$role}: {$content}";
        }

        return implode("\n\n", array_filter([
            "RÉSUMÉ DE CONVERSATION - {$conversation->title}",
            "Utilisateur: " . ($user ? $user->name : 'Utilisateur inconnu'),
            "Date conversation: " . $conversation->created_at->format('Y-m-d H:i:s'),
            "Nombre de messages: " . $messages->count(),
            "",
            "ÉCHANGES RÉCENTS:",
            implode("\n\n", $messageTexts)
        ]));
    }

    /**
     * Extract content from attachment based on file type
     */
    private function extractAttachmentContent(string $filePath, array $metadata = []): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        try {
            switch ($extension) {
                case 'txt':
                    return file_get_contents($filePath);
                
                case 'pdf':
                    // Use PDF extraction service
                    $pdfExtractor = app(\App\Services\PdfExtractionService::class);
                    $extractedData = $pdfExtractor->extractWithMetadata($filePath);
                    return $extractedData['content'] ?? '';
                
                case 'doc':
                case 'docx':
                    // For now, return metadata or use LLM for extraction
                    return "Document Word: " . basename($filePath) . "\n" . 
                           "Taille: " . (filesize($filePath) ?? 0) . " bytes\n" .
                           "Type: Document Microsoft Word\n" .
                           "Utilisez l'assistance LLM pour l'extraction complète du contenu.";
                
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'gif':
                case 'webp':
                case 'bmp':
                    // Use GPT-5-mini vision to extract content from images
                    return $this->extractImageContentWithGPT5Mini($filePath, $metadata);
                
                default:
                    return "Fichier: " . basename($filePath) . "\n" .
                           "Type: " . ($metadata['mime_type'] ?? 'Inconnu') . "\n" .
                           "Taille: " . (filesize($filePath) ?? 0) . " bytes\n" .
                           "Extension non supportée pour l'extraction automatique.";
            }
        } catch (\Exception $e) {
            Log::error('Failed to extract attachment content', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);
            return '';
        }
    }

    /**
     * Extract content from image using GPT-5-mini vision capabilities
     */
    private function extractImageContentWithGPT5Mini(string $imagePath, array $metadata = []): string
    {
        try {
            // Read and encode image to base64
            $imageContent = file_get_contents($imagePath);
            $base64Image = base64_encode($imageContent);
            $mimeType = $metadata['mime_type'] ?? mime_content_type($imagePath);
            
            // Prepare the request for GPT-5-mini with vision
            $response = \Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openai.api_key'),
                'Content-Type' => 'application/json',
            ])
            ->timeout(300)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4.1-nano',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "Tu es un expert en analyse d'images pour entrepreneurs ivoiriens. Analyse cette image et extrais toutes les informations pertinentes pour un contexte entrepreneurial. Décris le contenu en détail, identifie le texte visible, les données, graphiques, logos, personnes, produits, et tout élément pertinent. Sois précis et structuré dans ta description."
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Analyse cette image et extrais tout le contenu pertinent :'
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mimeType};base64,{$base64Image}",
                                    'detail' => 'high' // High detail for better extraction
                                ]
                            ]
                        ]
                    ]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.3 // Lower temperature for more factual extraction
            ]);

            if (!$response->successful()) {
                throw new \Exception('GPT-5-mini vision API error: ' . $response->body());
            }

            $result = $response->json();
            $extractedContent = $result['choices'][0]['message']['content'] ?? '';
            
            // Format the extracted content with metadata
            return implode("\n\n", [
                "=== ANALYSE D'IMAGE ===",
                "Fichier: " . basename($imagePath),
                "Type: Image ({$mimeType})",
                "Taille: " . number_format(filesize($imagePath) / 1024, 2) . " KB",
                "",
                "=== CONTENU EXTRAIT ===",
                $extractedContent,
                "",
                "=== MÉTADONNÉES ===",
                "Date d'analyse: " . now()->format('Y-m-d H:i:s'),
                "Modèle utilisé: GPT-5-mini avec vision",
                "Utilisateur: " . ($metadata['user_id'] ?? 'Non spécifié')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to extract image content with GPT-5-mini', [
                'image_path' => $imagePath,
                'error' => $e->getMessage()
            ]);
            
            // Fallback to basic metadata if vision extraction fails
            return "Image: " . basename($imagePath) . "\n" .
                   "Type: " . ($metadata['mime_type'] ?? 'Image') . "\n" .
                   "Taille: " . number_format(filesize($imagePath) / 1024, 2) . " KB\n" .
                   "Erreur d'extraction: " . $e->getMessage();
        }
    }
}