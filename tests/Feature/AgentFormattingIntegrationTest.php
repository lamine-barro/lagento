<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserConversation;
use App\Models\UserMessage;
use App\Services\MarkdownProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentFormattingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_response_renders_with_proper_formatting(): void
    {
        // Create test user and conversation
        $user = User::factory()->create();
        $conversation = UserConversation::create([
            'user_id' => $user->id,
            'title' => 'Test Conversation',
            'status' => 'active'
        ]);

        // Create message with Agent O formatted response
        $agentResponse = "## Opportunités de Financement pour votre Secteur

Voici les **meilleures opportunités** disponibles actuellement :

:::opportunity
**Programme d'Incubation Orange Fab**

Accompagnement et financement jusqu'à 100M FCFA

💰 **Type:** Incubation + Financement
📅 **Date limite:** 15 décembre 2024
🎯 **Secteurs:** Fintech, EdTech, HealthTech
:::

:::info
N'oubliez pas de préparer un pitch deck de 10 slides maximum avant de postuler.
:::

### Institutions Partenaires Recommandées

:::institution
**CEPICI - Centre de Promotion des Investissements**

Guichet unique pour la création d'entreprise en Côte d'Ivoire

📍 **Localisation:** Abidjan, Plateau
📞 **Contact:** +225 20 31 82 00
🌐 **Site web:** www.cepici.ci
:::

:::warning
Les délais de traitement peuvent être plus longs en fin d'année. Anticipez vos démarches.
:::";

        $message = UserMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $agentResponse
        ]);

        // Test that the view renders correctly
        $response = $this->actingAs($user)
            ->get(route('chat') . '?conversation=' . $conversation->id);

        $response->assertStatus(200);
        
        // Check that the HTML contains expected formatted elements
        $response->assertSee('Opportunités de Financement', false);
        $response->assertSee('opportunity-card', false);
        $response->assertSee('institution-card', false);
        $response->assertSee('alert-info', false);
        $response->assertSee('alert-warning', false);
        $response->assertSee('prose-enhanced', false);
        
        // Check specific card content
        $response->assertSee('Programme d\'Incubation Orange Fab', false);
        $response->assertSee('CEPICI', false);
        $response->assertSee('Postuler', false);
        $response->assertSee('Contacter', false);
    }

    public function test_markdown_processor_handles_complex_agent_response(): void
    {
        $processor = new MarkdownProcessor();
        
        $complexResponse = "# Analyse de votre Projet d'Entreprise

## Diagnostic Général

Votre projet présente un **fort potentiel** dans le secteur du numérique ivoirien.

### Points Forts Identifiés

1. **Innovation technologique** : Solution unique sur le marché local
2. **Équipe compétente** : Profils complémentaires et expérimentés  
3. **Marché porteur** : Forte demande identifiée

### Recommandations Prioritaires

:::success
Votre business model est solide et viable à long terme.
:::

:::opportunity
**Concours Orange Social Venture Prize**

Prix de l'innovation sociale en Afrique

💰 **Montant:** 25M FCFA + Accompagnement
📅 **Date limite:** 31 janvier 2025
🎯 **Focus:** Impact social et innovation
:::

### Prochaines Étapes

:::info
Préparez votre dossier de candidature en suivant ces étapes :

1. Finaliser le business plan
2. Constituer l'équipe définitive
3. Développer le MVP (Minimum Viable Product)
:::

#### Institutions à Contacter

:::institution
**Impact Hub Abidjan**

Incubateur d'entreprises à impact social

📍 **Localisation:** Abidjan, Cocody
📞 **Contact:** +225 07 08 09 10
🌐 **Site web:** impacthub.net/abidjan
:::

:::partner
**GreenTech Solutions**

👤 **Entrepreneur:** Koffi Assamoi
🏢 **Secteur:** Cleantech
📍 **Région:** Abidjan
🤝 **Synergie:** Partenariat technologique
:::

:::danger
Attention : Respectez impérativement les délais de candidature pour ne pas manquer les opportunités.
:::

> \"Le succès en entrepreneuriat nécessite de la **persévérance** et une *vision claire*.\"

**Bonne continuation dans votre parcours entrepreneurial !**";

        $output = $processor->process($complexResponse);

        // Verify all elements are properly formatted
        $this->assertStringContainsString('<div class="prose-enhanced">', $output);
        $this->assertStringContainsString('opportunity-card', $output);
        $this->assertStringContainsString('institution-card', $output);
        $this->assertStringContainsString('partner-card', $output);
        $this->assertStringContainsString('alert-success', $output);
        $this->assertStringContainsString('alert-info', $output);
        $this->assertStringContainsString('alert-danger', $output);
        
        // Check markdown elements
        $this->assertStringContainsString('<h1>', $output);
        $this->assertStringContainsString('<h2>', $output);
        $this->assertStringContainsString('<h3>', $output);
        $this->assertStringContainsString('<strong>', $output);
        $this->assertStringContainsString('<em>', $output);
        $this->assertStringContainsString('<ol>', $output);
        $this->assertStringContainsString('<blockquote', $output);
        
        // Check specific content
        $this->assertStringContainsString('Concours Orange Social Venture Prize', $output);
        $this->assertStringContainsString('Impact Hub Abidjan', $output);
        $this->assertStringContainsString('GreenTech Solutions', $output);
    }

    public function test_agent_output_maintains_design_system_consistency(): void
    {
        $processor = new MarkdownProcessor();
        
        // Test all card types with consistent design
        $input = <<<'EOD'
:::institution
**Test Institution**

Description test
:::

:::opportunity
**Test Opportunity**

Description test
:::

:::official-text
**Test Official Text**

Description test
:::

:::partner
**Test Partner**

Description test
:::
EOD;

        $output = $processor->process($input);
        
        // All cards should have consistent structure
        $this->assertEquals(4, substr_count($output, 'custom-card'));
        $this->assertEquals(4, substr_count($output, 'card-icon'));
        $this->assertEquals(4, substr_count($output, 'card-content'));
        $this->assertEquals(4, substr_count($output, 'card-actions'));
        
        // Each card type should have its specific styling
        $this->assertStringContainsString('institution-card', $output);
        $this->assertStringContainsString('opportunity-card', $output);
        $this->assertStringContainsString('official-text-card', $output);
        $this->assertStringContainsString('partner-card', $output);
        
        // Check action buttons consistency
        $this->assertStringContainsString('Contacter', $output);
        $this->assertStringContainsString('Postuler', $output);
        $this->assertStringContainsString('Télécharger', $output);
        $this->assertStringContainsString('Se connecter', $output);
    }
}