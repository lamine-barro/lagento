<?php

namespace Tests\Feature;

use App\Services\MarkdownProcessor;
use Tests\TestCase;

class MarkdownFormattingTest extends TestCase
{
    protected MarkdownProcessor $markdownProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->markdownProcessor = new MarkdownProcessor();
    }

    public function test_basic_markdown_formatting(): void
    {
        $input = "## Votre Projet d'Entreprise\n\nVoici **quelques conseils** pour votre projet :\n\n- Conseil 1\n- Conseil 2\n\n*Bonne chance !*";
        
        $output = $this->markdownProcessor->process($input);
        
        $this->assertStringContainsString('<h2>', $output);
        $this->assertStringContainsString('<strong>', $output);
        $this->assertStringContainsString('<ul>', $output);
        $this->assertStringContainsString('<em>', $output);
    }

    public function test_institution_card_rendering(): void
    {
        $input = ":::institution\n**CEPICI**\n\nCentre de Promotion des Investissements\n\n📍 **Localisation:** Abidjan, Plateau\n📞 **Contact:** +225 20 30 40 50\n:::\n";
        
        $output = $this->markdownProcessor->process($input);
        
        $this->assertStringContainsString('institution-card', $output);
        $this->assertStringContainsString('building-2', $output);
        $this->assertStringContainsString('Contacter', $output);
        $this->assertStringContainsString('CEPICI', $output);
    }

    public function test_opportunity_card_rendering(): void
    {
        $input = ":::opportunity\n**Programme d'Incubation 2024**\n\nSoutien aux startups tech\n\n💰 **Type:** Incubation\n📅 **Date limite:** 31 décembre 2024\n:::\n";
        
        $output = $this->markdownProcessor->process($input);
        
        $this->assertStringContainsString('opportunity-card', $output);
        $this->assertStringContainsString('target', $output);
        $this->assertStringContainsString('Postuler', $output);
        $this->assertStringContainsString('Programme d\'Incubation', $output);
    }

    public function test_official_text_card_rendering(): void
    {
        $input = ":::official-text\n**Loi sur les Startups**\n\nRéglementation des entreprises innovantes\n\n📜 **Type:** Loi ordinaire\n📅 **Date publication:** 15 janvier 2024\n:::\n";
        
        $output = $this->markdownProcessor->process($input);
        
        $this->assertStringContainsString('official-text-card', $output);
        $this->assertStringContainsString('scale', $output);
        $this->assertStringContainsString('Télécharger', $output);
        $this->assertStringContainsString('Loi sur les Startups', $output);
    }

    public function test_partner_card_rendering(): void
    {
        $input = ":::partner\n**EcoTech Solutions**\n\n👤 **Entrepreneur:** Marie Kouassi\n🏢 **Secteur:** CleanTech\n📍 **Région:** Abidjan\n:::\n";
        
        $output = $this->markdownProcessor->process($input);
        
        $this->assertStringContainsString('partner-card', $output);
        $this->assertStringContainsString('users', $output);
        $this->assertStringContainsString('Se connecter', $output);
        $this->assertStringContainsString('EcoTech Solutions', $output);
    }

    public function test_alert_rendering(): void
    {
        $alerts = [
            ":::info\nInformation importante\n:::" => 'alert-info',
            ":::warning\nAttention requise\n:::" => 'alert-warning',
            ":::danger\nErreur critique\n:::" => 'alert-danger',
            ":::success\nOpération réussie\n:::" => 'alert-success'
        ];

        foreach ($alerts as $input => $expectedClass) {
            $output = $this->markdownProcessor->process($input);
            
            $this->assertStringContainsString('alert', $output);
            $this->assertStringContainsString($expectedClass, $output);
        }
    }

    public function test_complex_response_with_multiple_elements(): void
    {
        $input = "## Opportunités de Financement\n\nVoici les **meilleures opportunités** pour votre secteur :\n\n:::opportunity\n**Fonds d'Amorçage Tech**\n\nFinancement jusqu'à 50M FCFA\n\n💰 **Type:** Subvention\n📅 **Date limite:** 30 juin 2024\n:::\n\n:::info\nN'oubliez pas de préparer votre business plan avant de postuler.\n:::\n\n### Institutions Partenaires\n\n:::institution\n**Orange Fab CI**\n\nAccélérateur Orange en Côte d'Ivoire\n\n📍 **Localisation:** Abidjan, Cocody\n:::\n";
        
        $output = $this->markdownProcessor->process($input);
        
        // Check all elements are present
        $this->assertStringContainsString('<h2>', $output);
        $this->assertStringContainsString('<h3>', $output);
        $this->assertStringContainsString('opportunity-card', $output);
        $this->assertStringContainsString('institution-card', $output);
        $this->assertStringContainsString('alert-info', $output);
        $this->assertStringContainsString('Fonds d\'Amorçage', $output);
        $this->assertStringContainsString('Orange Fab CI', $output);
    }

    public function test_markdown_structure_preservation(): void
    {
        $input = "# Titre Principal\n\n## Sous-titre\n\nParagraphe avec **gras** et *italique*.\n\n### Liste\n\n1. Élément 1\n2. Élément 2\n\n> Citation importante\n\n`code inline`";
        
        $output = $this->markdownProcessor->process($input);
        
        $this->assertStringContainsString('prose-enhanced', $output);
        $this->assertStringContainsString('<h1>', $output);
        $this->assertStringContainsString('<h2>', $output);
        $this->assertStringContainsString('<h3>', $output);
        $this->assertStringContainsString('<strong>', $output);
        $this->assertStringContainsString('<em>', $output);
        $this->assertStringContainsString('<ol>', $output);
        $this->assertStringContainsString('<blockquote', $output);
        $this->assertStringContainsString('<code>', $output);
    }
}