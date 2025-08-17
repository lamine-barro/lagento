<?php

namespace Tests\Feature;

use App\Constants\BusinessConstants;
use App\Models\User;
use App\Models\UserConversation;
use App\Models\UserMessage;
use App\Models\Project;
use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_has_all_required_fields(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+225 01 23 45 67',
            'profile_type' => 'entrepreneur',
            'verification_status' => 'verified',
            'company_name' => 'Test Company',
            'business_sector' => 'NUMERIQUE',
            'business_stage' => 'CROISSANCE',
            'team_size' => '1-5',
            'monthly_revenue' => 'DE_5_A_50M',
            'main_challenges' => ['funding', 'marketing'],
            'objectives' => ['growth'],
            'preferred_support' => ['mentoring'],
            'onboarding_completed' => true
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'phone' => '+225 01 23 45 67',
            'profile_type' => 'entrepreneur',
            'verification_status' => 'verified',
            'company_name' => 'Test Company',
            'onboarding_completed' => true
        ]);

        $this->assertEquals(['funding', 'marketing'], $user->main_challenges);
        $this->assertEquals(['growth'], $user->objectives);
        $this->assertEquals(['mentoring'], $user->preferred_support);
    }

    public function test_conversation_model_with_correct_status(): void
    {
        $user = User::factory()->create();
        
        $conversation = UserConversation::create([
            'user_id' => $user->id,
            'title' => 'Test Conversation',
            'status' => 'active',
            'satisfaction_score' => 4,
            'message_count' => 0,
            'is_pinned' => false,
            'metadata' => [
                'device_type' => 'mobile',
                'location' => 'Abidjan'
            ]
        ]);

        $this->assertDatabaseHas('user_conversations', [
            'user_id' => $user->id,
            'title' => 'Test Conversation',
            'status' => 'active',
            'satisfaction_score' => 4,
            'is_pinned' => false
        ]);

        $this->assertEquals(['device_type' => 'mobile', 'location' => 'Abidjan'], $conversation->metadata);
    }

    public function test_message_model_with_content_alias(): void
    {
        $user = User::factory()->create();
        $conversation = UserConversation::create([
            'user_id' => $user->id,
            'title' => 'Test',
            'status' => 'active'
        ]);

        $message = UserMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Test message content',
            'attachments' => [
                [
                    'type' => 'document',
                    'nom_fichier' => 'test.pdf',
                    'url' => '/storage/test.pdf',
                    'taille' => 1024,
                    'mime_type' => 'application/pdf'
                ]
            ],
            'executed_tools' => [
                [
                    'type' => 'recherche_web',
                    'resultat' => 'search results',
                    'duree_ms' => 1500
                ]
            ],
            'tokens_used' => 150,
            'is_retried' => false,
            'is_copied' => false
        ]);

        $this->assertEquals('Test message content', $message->content);
        $this->assertEquals('Test message content', $message->text_content);
        $this->assertIsArray($message->attachments);
        $this->assertIsArray($message->executed_tools);
    }

    public function test_business_constants_are_available(): void
    {
        $this->assertIsArray(BusinessConstants::SECTEURS);
        $this->assertArrayHasKey('NUMERIQUE', BusinessConstants::SECTEURS);
        $this->assertEquals('Télécoms & Services numériques', BusinessConstants::SECTEURS['NUMERIQUE']);

        $this->assertIsArray(BusinessConstants::REGIONS);
        $this->assertArrayHasKey('Abidjan', BusinessConstants::REGIONS);

        $this->assertIsArray(BusinessConstants::CONVERSATION_STATUS);
        $this->assertContains('active', BusinessConstants::CONVERSATION_STATUS);
        $this->assertContains('archivée', BusinessConstants::CONVERSATION_STATUS);
        $this->assertContains('en_attente', BusinessConstants::CONVERSATION_STATUS);
    }

    public function test_config_constants_work(): void
    {
        $secteurs = config('constants.SECTEURS');
        $this->assertIsArray($secteurs);
        $this->assertArrayHasKey('NUMERIQUE', $secteurs);

        $teamSizes = config('constants.TEAM_SIZES');
        $this->assertIsArray($teamSizes);
        $this->assertContains('1-5', $teamSizes);

        $regions = config('constants.REGIONS');
        $this->assertIsArray($regions);
        $this->assertArrayHasKey('Abidjan', $regions);
    }

    public function test_project_model_consistency(): void
    {
        $project = Project::create([
            'project_name' => 'Test Project',
            'company_name' => 'Test Company',
            'description' => 'A test project',
            'formalized' => 'oui',
            'incorporation_year' => 2023,
            'sectors' => ['NUMERIQUE'],
            'targets' => ['B2B'],
            'maturity' => 'CROISSANCE',
            'region' => 'Abidjan',
            'team_size' => '1-5',
            'newsletter_opt_in' => true
        ]);

        $this->assertDatabaseHas('projects', [
            'project_name' => 'Test Project',
            'formalized' => 'oui',
            'newsletter_opt_in' => true
        ]);

        $this->assertEquals(['NUMERIQUE'], $project->sectors);
        $this->assertEquals(['B2B'], $project->targets);
    }

    public function test_analytics_integration_consistency(): void
    {
        $user = User::factory()->create([
            'name' => 'Analytics User',
            'email' => 'analytics@test.com',
            'phone' => '+225 01 23 45 67',
            'company_name' => 'Analytics Co',
            'business_sector' => 'NUMERIQUE',
            'onboarding_completed' => true
        ]);

        // Test that analytics can be created and linked
        $analytics = $user->analytics()->create([
            'generated_at' => now(),
            'expires_at' => now()->addDays(30),
            'entrepreneur_profile' => [
                'completion_score' => 85,
                'business_info' => [
                    'business_name' => 'Analytics Co',
                    'business_sector' => 'NUMERIQUE'
                ]
            ],
            'metadata' => [
                'chat_interactions' => [
                    'total_messages' => 10,
                    'agent_usage' => ['principal' => 8, 'suggestions' => 5]
                ]
            ]
        ]);

        $this->assertNotNull($analytics);
        $this->assertEquals($user->id, $analytics->user_id);
        $this->assertEquals(85, $analytics->entrepreneur_profile['completion_score']);
    }
}