<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAnalytics;
use App\Models\Project;
use App\Models\UserMessage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UserAnalyticsService
{
    /**
     * Update entrepreneur profile analytics based on onboarding data
     */
    public function updateEntrepreneurProfile(User $user, array $onboardingData): void
    {
        try {
            $analytics = $this->getOrCreateUserAnalytics($user);
            
            // Enrich with lightweight LLM pass (gpt-5-mini) to extract salient tags and summary
            $lmSummary = $this->summarizeBusinessData($onboardingData);

            $profile = [
                'niveau_global' => $lmSummary['level'] ?? null,
                'score_potentiel' => $lmSummary['potential_score'] ?? null,
                'forces' => $lmSummary['strengths'] ?? [],
                'axes_progression' => $lmSummary['improvements'] ?? [],
                'besoins_formation' => $lmSummary['training_needs'] ?? [],
                'profil_type' => $lmSummary['profile_type'] ?? null,
                'basic_info' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profile_type' => $user->profile_type,
                    'verification_status' => $user->verification_status,
                    'updated_at' => now()->toISOString()
                ],
                'business_info' => array_merge($onboardingData, [
                    'llm_summary' => $lmSummary['summary'] ?? null,
                    'keywords' => $lmSummary['keywords'] ?? [],
                    'risk_flags' => $lmSummary['risks'] ?? [],
                ]),
                'completion_score' => $this->calculateProfileCompletion($user, $onboardingData),
                'engagement_level' => $this->calculateEngagementLevel($user),
                'last_activity' => now()->toISOString()
            ];

            // derive nombre_fondateurs total si possible
            $male = data_get($onboardingData, 'num_founders_male');
            $female = data_get($onboardingData, 'num_founders_female');
            if (is_numeric($male) || is_numeric($female)) {
                $profile['business_info']['nombre_fondateurs'] = (int)max(0, (int)$male) + (int)max(0, (int)$female);
            }

            $analytics->update([
                'entrepreneur_profile' => $profile,
                'generated_at' => now(),
                'expires_at' => now()->addDays(30),
                'metadata' => array_merge($analytics->metadata ?? [], [
                    'profile_updates' => ($analytics->metadata['profile_updates'] ?? 0) + 1,
                    'last_profile_update' => now()->toISOString()
                ])
            ]);

            Log::info("Entrepreneur profile updated for user {$user->id}");
            
        } catch (\Exception $e) {
            Log::error("Failed to update entrepreneur profile for user {$user->id}: " . $e->getMessage());
        }
    }

    private function summarizeBusinessData(array $data): array
    {
        try {
            $text = json_encode($data, JSON_UNESCAPED_UNICODE);
            if (!$text || strlen($text) < 10) return [];

            $messages = [
                [
                    'role' => 'system',
                    'content' => 'Tu es un analyste business. Sur la base des données de projet et profil, génère un JSON STRICT avec: summary (3 phrases max), keywords (5 FR), risks (≤3), level (débutant|confirmé|expert), potential_score (0-100), strengths[{domaine,description}], improvements[{domaine,action_suggeree,impact}], training_needs[string], profile_type (innovateur|gestionnaire|commercial|artisan|commerçant). Réponds UNIQUEMENT ce JSON.'
                ],
                [
                    'role' => 'user',
                    'content' => $text
                ]
            ];

            $lm = app(\App\Services\LanguageModelService::class);
            $raw = $lm->chat($messages, 'gpt-5-mini', 0.3, 5000);
            $parsed = json_decode($raw, true);
            if (is_array($parsed)) return $parsed;
        } catch (\Throwable $e) {
            // swallow and fallback
        }
        return [];
    }

    /**
     * Track user interaction with chat/agents
     */
    public function trackChatInteraction(User $user, array $interactionData): void
    {
        try {
            $analytics = $this->getOrCreateUserAnalytics($user);
            
            $currentMetadata = $analytics->metadata ?? [];
            
            // Update interaction stats
            $interactions = $currentMetadata['chat_interactions'] ?? [
                'total_messages' => 0,
                'total_sessions' => 0,
                'agent_usage' => [
                    'principal' => 0,
                    'suggestions' => 0,
                    'title_generation' => 0
                ],
                'topics_discussed' => [],
                'avg_session_length' => 0,
                'last_interaction' => null,
                'user_satisfaction' => []
            ];

            $interactions['total_messages']++;
            $interactions['last_interaction'] = now()->toISOString();
            
            // Track agent usage
            if (isset($interactionData['agent_type'])) {
                $interactions['agent_usage'][$interactionData['agent_type']] = 
                    ($interactions['agent_usage'][$interactionData['agent_type']] ?? 0) + 1;
            }

            // Extract and track topics
            if (isset($interactionData['tools_used'])) {
                foreach ($interactionData['tools_used'] as $tool) {
                    if (!in_array($tool, $interactions['topics_discussed'])) {
                        $interactions['topics_discussed'][] = $tool;
                    }
                }
            }

            $analytics->update([
                'metadata' => array_merge($currentMetadata, [
                    'chat_interactions' => $interactions,
                    'last_activity' => now()->toISOString()
                ])
            ]);

            Log::info("Chat interaction tracked for user {$user->id}");
            
        } catch (\Exception $e) {
            Log::error("Failed to track chat interaction for user {$user->id}: " . $e->getMessage());
        }
    }

    /**
     * Update analytics when user uploads data sources
     */
    public function trackDataSourceUpload(User $user, array $uploadData): void
    {
        try {
            $analytics = $this->getOrCreateUserAnalytics($user);
            
            $currentMetadata = $analytics->metadata ?? [];
            
            $dataSources = $currentMetadata['data_sources'] ?? [
                'total_uploads' => 0,
                'file_types' => [],
                'total_size_mb' => 0,
                'upload_history' => [],
                'categories' => []
            ];

            $dataSources['total_uploads']++;
            $dataSources['total_size_mb'] += $uploadData['size_mb'] ?? 0;
            
            // Track file types
            if (isset($uploadData['file_type'])) {
                $fileType = $uploadData['file_type'];
                $dataSources['file_types'][$fileType] = ($dataSources['file_types'][$fileType] ?? 0) + 1;
            }

            // Add to upload history
            $dataSources['upload_history'][] = [
                'filename' => $uploadData['filename'] ?? 'unknown',
                'type' => $uploadData['file_type'] ?? 'unknown',
                'size_mb' => $uploadData['size_mb'] ?? 0,
                'uploaded_at' => now()->toISOString(),
                'category' => $uploadData['category'] ?? 'general'
            ];

            // Keep only last 50 uploads in history
            if (count($dataSources['upload_history']) > 50) {
                $dataSources['upload_history'] = array_slice($dataSources['upload_history'], -50);
            }

            $analytics->update([
                'metadata' => array_merge($currentMetadata, [
                    'data_sources' => $dataSources,
                    'last_activity' => now()->toISOString()
                ])
            ]);

            Log::info("Data source upload tracked for user {$user->id}");
            
        } catch (\Exception $e) {
            Log::error("Failed to track data source upload for user {$user->id}: " . $e->getMessage());
        }
    }

    /**
     * Generate comprehensive user insights
     */
    public function generateUserInsights(User $user): array
    {
        try {
            $analytics = $this->getOrCreateUserAnalytics($user);
            
            $insights = [
                'user_journey' => [
                    'registration_date' => $user->created_at->toISOString(),
                    'days_since_registration' => $user->created_at->diffInDays(now()),
                    'onboarding_completed' => !empty($analytics->entrepreneur_profile),
                    'profile_completion' => $this->calculateProfileCompletion($user, $analytics->entrepreneur_profile['business_info'] ?? []),
                    'engagement_level' => $this->calculateEngagementLevel($user)
                ],
                'activity_summary' => [
                    'total_chat_messages' => $analytics->metadata['chat_interactions']['total_messages'] ?? 0,
                    'total_uploads' => $analytics->metadata['data_sources']['total_uploads'] ?? 0,
                    'last_activity' => $analytics->metadata['last_activity'] ?? null,
                    'most_used_agent' => $this->getMostUsedAgent($analytics),
                    'favorite_topics' => $this->getTopTopics($analytics)
                ],
                'recommendations' => $this->generateRecommendations($user, $analytics),
                'generated_at' => now()->toISOString()
            ];

            // Update analytics with insights
            $analytics->update([
                'executive_summary' => $insights,
                'generated_at' => now(),
                'expires_at' => now()->addDays(7)
            ]);

            return $insights;
            
        } catch (\Exception $e) {
            Log::error("Failed to generate user insights for user {$user->id}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get or create user analytics record
     */
    private function getOrCreateUserAnalytics(User $user): UserAnalytics
    {
        return UserAnalytics::firstOrCreate(
            ['user_id' => $user->id],
            [
                'generated_at' => now(),
                'expires_at' => now()->addDays(30),
                'metadata' => []
            ]
        );
    }

    /**
     * Calculate profile completion percentage
     */
    private function calculateProfileCompletion(User $user, array $onboardingData): int
    {
        $requiredFields = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'business_name' => $onboardingData['business_name'] ?? null,
            'business_sector' => $onboardingData['business_sector'] ?? null,
            'business_stage' => $onboardingData['business_stage'] ?? null,
            'target_market' => $onboardingData['target_market'] ?? null,
            'funding_needs' => $onboardingData['funding_needs'] ?? null
        ];

        $completedFields = array_filter($requiredFields, fn($value) => !empty($value));
        
        return round((count($completedFields) / count($requiredFields)) * 100);
    }

    /**
     * Calculate user engagement level
     */
    private function calculateEngagementLevel(User $user): string
    {
        $daysSinceRegistration = $user->created_at->diffInDays(now());
        $totalMessages = UserMessage::where('conversation_id', function($query) use ($user) {
            $query->select('id')->from('user_conversations')->where('user_id', $user->id);
        })->count();

        if ($daysSinceRegistration === 0) {
            return $totalMessages > 0 ? 'high' : 'new';
        }

        $messagesPerDay = $totalMessages / max($daysSinceRegistration, 1);

        if ($messagesPerDay >= 5) return 'high';
        if ($messagesPerDay >= 2) return 'medium';
        if ($messagesPerDay >= 0.5) return 'low';
        
        return 'inactive';
    }

    /**
     * Get most used agent
     */
    private function getMostUsedAgent(UserAnalytics $analytics): ?string
    {
        $agentUsage = $analytics->metadata['chat_interactions']['agent_usage'] ?? [];
        
        if (empty($agentUsage)) return null;
        
        return array_keys($agentUsage, max($agentUsage))[0];
    }

    /**
     * Get top discussion topics
     */
    private function getTopTopics(UserAnalytics $analytics): array
    {
        return array_slice($analytics->metadata['chat_interactions']['topics_discussed'] ?? [], 0, 5);
    }

    /**
     * Generate personalized recommendations
     */
    private function generateRecommendations(User $user, UserAnalytics $analytics): array
    {
        $recommendations = [];
        
        $profileCompletion = $analytics->entrepreneur_profile['completion_score'] ?? 0;
        $chatInteractions = $analytics->metadata['chat_interactions']['total_messages'] ?? 0;
        $dataUploads = $analytics->metadata['data_sources']['total_uploads'] ?? 0;

        // Profile completion recommendations
        if ($profileCompletion < 80) {
            $recommendations[] = [
                'type' => 'profile_completion',
                'priority' => 'high',
                'title' => 'Complétez votre profil entrepreneur',
                'description' => 'Votre profil est complété à ' . $profileCompletion . '%. Complétez-le pour de meilleures recommandations.',
                'action' => 'complete_profile'
            ];
        }

        // Engagement recommendations
        if ($chatInteractions < 5) {
            $recommendations[] = [
                'type' => 'engagement',
                'priority' => 'medium',
                'title' => 'Explorez LAgentO davantage',
                'description' => 'Posez des questions sur la création d\'entreprise, les financements ou les réglementations.',
                'action' => 'start_chat'
            ];
        }

        // Data source recommendations
        if ($dataUploads === 0) {
            $recommendations[] = [
                'type' => 'data_sources',
                'priority' => 'medium',
                'title' => 'Ajoutez vos documents business',
                'description' => 'Téléchargez votre business plan ou documents pour des analyses personnalisées.',
                'action' => 'upload_documents'
            ];
        }

        return $recommendations;
    }
}