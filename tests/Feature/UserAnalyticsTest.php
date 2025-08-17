<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserAnalytics;
use App\Services\UserAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected UserAnalyticsService $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsService = $this->app->make(UserAnalyticsService::class);
    }

    public function test_entrepreneur_profile_can_be_updated(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+225 01 23 45 67'
        ]);

        $onboardingData = [
            'business_name' => 'Tech Startup',
            'business_sector' => 'technology',
            'business_stage' => 'idea',
            'team_size' => '1-5',
            'monthly_revenue' => '0-1000',
            'main_challenges' => ['funding', 'marketing'],
            'objectives' => ['growth', 'expansion'],
            'preferred_support' => ['mentoring', 'networking']
        ];

        $this->analyticsService->updateEntrepreneurProfile($user, $onboardingData);

        $this->assertDatabaseHas('user_analytics', [
            'user_id' => $user->id
        ]);

        $analytics = UserAnalytics::where('user_id', $user->id)->first();
        $this->assertNotNull($analytics->entrepreneur_profile);
        $this->assertEquals('Tech Startup', $analytics->entrepreneur_profile['business_info']['business_name']);
    }

    public function test_chat_interaction_can_be_tracked(): void
    {
        $user = User::factory()->create();

        $interactionData = [
            'agent_type' => 'principal',
            'message_length' => 100,
            'tools_used' => ['recherche_semantique'],
            'response_length' => 250,
            'has_attachment' => false,
            'conversation_id' => 1
        ];

        $this->analyticsService->trackChatInteraction($user, $interactionData);

        $analytics = UserAnalytics::where('user_id', $user->id)->first();
        $this->assertNotNull($analytics);
        $this->assertEquals(1, $analytics->metadata['chat_interactions']['total_messages']);
        $this->assertEquals(1, $analytics->metadata['chat_interactions']['agent_usage']['principal']);
    }

    public function test_data_source_upload_can_be_tracked(): void
    {
        $user = User::factory()->create();

        $uploadData = [
            'filename' => 'business_plan.pdf',
            'file_type' => 'application/pdf',
            'size_mb' => 2.5,
            'category' => 'business_plan'
        ];

        $this->analyticsService->trackDataSourceUpload($user, $uploadData);

        $analytics = UserAnalytics::where('user_id', $user->id)->first();
        $this->assertNotNull($analytics);
        $this->assertEquals(1, $analytics->metadata['data_sources']['total_uploads']);
        $this->assertEquals(2.5, $analytics->metadata['data_sources']['total_size_mb']);
        $this->assertEquals(1, $analytics->metadata['data_sources']['file_types']['application/pdf']);
    }

    public function test_user_insights_can_be_generated(): void
    {
        $user = User::factory()->create();

        // Add some sample data
        $this->analyticsService->updateEntrepreneurProfile($user, [
            'business_name' => 'Test Business',
            'business_sector' => 'tech'
        ]);

        $this->analyticsService->trackChatInteraction($user, [
            'agent_type' => 'principal',
            'tools_used' => ['recherche_web']
        ]);

        $insights = $this->analyticsService->generateUserInsights($user);

        $this->assertNotEmpty($insights);
        $this->assertArrayHasKey('user_journey', $insights);
        $this->assertArrayHasKey('activity_summary', $insights);
        $this->assertArrayHasKey('recommendations', $insights);
    }

    public function test_profile_completion_calculation(): void
    {
        $user = User::factory()->create([
            'name' => 'Complete User',
            'email' => 'complete@example.com',
            'phone' => '+225 01 23 45 67'
        ]);

        $completeOnboardingData = [
            'business_name' => 'Complete Business',
            'business_sector' => 'tech',
            'business_stage' => 'growth',
            'target_market' => 'B2B',
            'funding_needs' => '10000-50000'
        ];

        $this->analyticsService->updateEntrepreneurProfile($user, $completeOnboardingData);

        $analytics = UserAnalytics::where('user_id', $user->id)->first();
        $completionScore = $analytics->entrepreneur_profile['completion_score'];

        $this->assertGreaterThan(80, $completionScore);
    }
}