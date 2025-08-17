<?php

namespace Tests\Feature;

use App\Agents\AgentPrincipal;
use App\Agents\AgentSuggestionsConversation;
use App\Agents\AgentTitreConversation;
use App\Services\AgentService;
use App\Services\EmbeddingService;
use App\Services\SemanticSearchService;
use App\Models\User;
use App\Models\UserConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentSystemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the landing page loads correctly
     */
    public function test_landing_page_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewIs('landing');
    }

    /**
     * Test that agents can be instantiated correctly
     */
    public function test_agents_can_be_instantiated(): void
    {
        $this->assertInstanceOf(AgentPrincipal::class, app(AgentPrincipal::class));
        $this->assertInstanceOf(AgentSuggestionsConversation::class, app(AgentSuggestionsConversation::class));
        $this->assertInstanceOf(AgentTitreConversation::class, app(AgentTitreConversation::class));
    }

    /**
     * Test that services can be instantiated correctly
     */
    public function test_services_can_be_instantiated(): void
    {
        $this->assertInstanceOf(AgentService::class, app(AgentService::class));
        $this->assertInstanceOf(EmbeddingService::class, app(EmbeddingService::class));
        $this->assertInstanceOf(SemanticSearchService::class, app(SemanticSearchService::class));
    }

    /**
     * Test agent configuration
     */
    public function test_agent_configurations(): void
    {
        $agentPrincipal = app(AgentPrincipal::class);
        $reflection = new \ReflectionClass($agentPrincipal);
        $method = $reflection->getMethod('getConfig');
        $method->setAccessible(true);
        $config = $method->invoke($agentPrincipal);

        $this->assertEquals('gpt-5-mini', $config['model']);
        $this->assertEquals('precision', $config['strategy']);
        $this->assertIsArray($config['tools']);
        $this->assertContains('gestion_base_donnees', $config['tools']);

        $agentSuggestions = app(AgentSuggestionsConversation::class);
        $reflection = new \ReflectionClass($agentSuggestions);
        $method = $reflection->getMethod('getConfig');
        $method->setAccessible(true);
        $config = $method->invoke($agentSuggestions);

        $this->assertEquals('gpt-5-nano', $config['model']);
        $this->assertEquals('fast', $config['strategy']);

        $agentTitre = app(AgentTitreConversation::class);
        $reflection = new \ReflectionClass($agentTitre);
        $method = $reflection->getMethod('getConfig');
        $method->setAccessible(true);
        $config = $method->invoke($agentTitre);

        $this->assertEquals('gpt-5-nano', $config['model']);
        $this->assertEquals('fast', $config['strategy']);
    }

    /**
     * Test chat route requires authentication
     */
    public function test_chat_requires_authentication(): void
    {
        $response = $this->get('/chat');
        $response->assertStatus(302); // Redirect to login
    }

    /**
     * Test authenticated user can access chat
     */
    public function test_authenticated_user_can_access_chat(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $response = $this->actingAs($user)->get('/chat');
        $response->assertStatus(200);
        $response->assertViewIs('chat');
    }

    /**
     * Test conversation creation
     */
    public function test_conversation_is_created_for_new_user(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $this->assertEquals(0, UserConversation::count());

        $this->actingAs($user)->get('/chat');

        $this->assertEquals(1, UserConversation::count());
        $conversation = UserConversation::first();
        $this->assertEquals($user->id, $conversation->user_id);
        $this->assertEquals('Nouvelle conversation', $conversation->title);
    }
}
