<?php

namespace App\Services;

use Illuminate\Support\Str;

class MarkdownProcessor
{
    public function process(string $content): string
    {
        $placeholders = [];
        
        // First, extract custom cards and replace with placeholders
        $content = $this->extractCustomCards($content, $placeholders);
        
        // Then process standard markdown
        $content = Str::markdown($content);
        
        // Replace placeholders with rendered cards
        $content = $this->replacePlaceholders($content, $placeholders);
        
        // Add custom CSS classes
        $content = $this->addCustomClasses($content);
        
        return $content;
    }

    protected function extractCustomCards(string $content, array &$placeholders): string
    {
        // Extract institution cards
        $content = preg_replace_callback(
            '/:::institution\n(.*?)\n:::/s',
            function($matches) use (&$placeholders) {
                $placeholder = 'PLACEHOLDER_INSTITUTION_' . count($placeholders);
                $placeholders[$placeholder] = $this->renderInstitutionCard($matches);
                return $placeholder;
            },
            $content
        );

        // Extract opportunity cards
        $content = preg_replace_callback(
            '/:::opportunity\n(.*?)\n:::/s',
            function($matches) use (&$placeholders) {
                $placeholder = 'PLACEHOLDER_OPPORTUNITY_' . count($placeholders);
                $placeholders[$placeholder] = $this->renderOpportunityCard($matches);
                return $placeholder;
            },
            $content
        );

        // Extract official text cards
        $content = preg_replace_callback(
            '/:::official-text\n(.*?)\n:::/s',
            function($matches) use (&$placeholders) {
                $placeholder = 'PLACEHOLDER_OFFICIAL_TEXT_' . count($placeholders);
                $placeholders[$placeholder] = $this->renderOfficialTextCard($matches);
                return $placeholder;
            },
            $content
        );

        // Extract partner cards
        $content = preg_replace_callback(
            '/:::partner\n(.*?)\n:::/s',
            function($matches) use (&$placeholders) {
                $placeholder = 'PLACEHOLDER_PARTNER_' . count($placeholders);
                $placeholders[$placeholder] = $this->renderPartnerCard($matches);
                return $placeholder;
            },
            $content
        );

        // Extract alert boxes
        $content = preg_replace_callback(
            '/:::(info|warning|danger|success)\n(.*?)\n:::/s',
            function($matches) use (&$placeholders) {
                $placeholder = 'PLACEHOLDER_ALERT_' . count($placeholders);
                $placeholders[$placeholder] = $this->renderAlert($matches);
                return $placeholder;
            },
            $content
        );

        return $content;
    }

    protected function replacePlaceholders(string $content, array $placeholders): string
    {
        foreach ($placeholders as $placeholder => $html) {
            $content = str_replace('<p>' . $placeholder . '</p>', $html, $content);
            $content = str_replace($placeholder, $html, $content);
        }
        return $content;
    }

    protected function renderInstitutionCard(array $matches): string
    {
        $cardContent = trim($matches[1]);
        
        return '<div class="institution-card custom-card">
                    <div class="card-icon">
                        <i data-lucide="building-2" class="w-5 h-5"></i>
                    </div>
                    <div class="card-content">
                        ' . Str::markdown($cardContent) . '
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-sm btn-outline-primary" onclick="contactInstitution()">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                            Contacter
                        </button>
                        <button class="btn btn-sm btn-ghost" onclick="viewMore()">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                            En savoir plus
                        </button>
                    </div>
                </div>';
    }

    protected function renderOpportunityCard(array $matches): string
    {
        $cardContent = trim($matches[1]);
        
        return '<div class="opportunity-card custom-card">
                    <div class="card-icon">
                        <i data-lucide="target" class="w-5 h-5"></i>
                    </div>
                    <div class="card-content">
                        ' . Str::markdown($cardContent) . '
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-sm btn-primary" onclick="applyOpportunity()">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            Postuler
                        </button>
                        <button class="btn btn-sm btn-ghost" onclick="saveOpportunity()">
                            <i data-lucide="bookmark" class="w-4 h-4"></i>
                            Sauvegarder
                        </button>
                    </div>
                </div>';
    }

    protected function renderOfficialTextCard(array $matches): string
    {
        $cardContent = trim($matches[1]);
        
        return '<div class="official-text-card custom-card">
                    <div class="card-icon">
                        <i data-lucide="scale" class="w-5 h-5"></i>
                    </div>
                    <div class="card-content">
                        ' . Str::markdown($cardContent) . '
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-sm btn-outline-primary" onclick="downloadText()">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            Télécharger
                        </button>
                        <button class="btn btn-sm btn-ghost" onclick="viewFullText()">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                            Lire
                        </button>
                    </div>
                </div>';
    }

    protected function renderPartnerCard(array $matches): string
    {
        $cardContent = trim($matches[1]);
        
        return '<div class="partner-card custom-card">
                    <div class="card-icon">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <div class="card-content">
                        ' . Str::markdown($cardContent) . '
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-sm btn-primary" onclick="connectPartner()">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            Se connecter
                        </button>
                        <button class="btn btn-sm btn-ghost" onclick="viewProfile()">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            Voir profil
                        </button>
                    </div>
                </div>';
    }

    protected function renderAlert(array $matches): string
    {
        $type = $matches[1];
        $content = trim($matches[2]);
        
        $alertClass = $this->getAlertClass($type);
        $icon = $this->getAlertIcon($type);
        
        return '<div class="alert ' . $alertClass . '">
                    <div class="alert-icon">
                        <i data-lucide="' . $icon . '" class="w-5 h-5"></i>
                    </div>
                    <div class="alert-content">
                        ' . Str::markdown($content) . '
                    </div>
                </div>';
    }

    protected function getAlertClass(string $type): string
    {
        return match($type) {
            'info' => 'alert-info',
            'warning' => 'alert-warning',
            'danger' => 'alert-danger',
            'success' => 'alert-success',
            default => 'alert-info'
        };
    }

    protected function getAlertIcon(string $type): string
    {
        return match($type) {
            'info' => 'info',
            'warning' => 'alert-triangle',
            'danger' => 'alert-circle',
            'success' => 'check-circle',
            default => 'info'
        };
    }

    protected function addCustomClasses(string $content): string
    {
        // Wrap entire content in prose-enhanced div
        $content = '<div class="prose-enhanced">' . $content . '</div>';
        
        // Add responsive classes to tables
        $content = preg_replace(
            '/<table[^>]*>/',
            '<div class="table-responsive"><table class="table">',
            $content
        );
        $content = str_replace('</table>', '</table></div>', $content);
        
        // Add classes to blockquotes
        $content = preg_replace(
            '/<blockquote[^>]*>/',
            '<blockquote class="blockquote">',
            $content
        );
        
        return $content;
    }
}