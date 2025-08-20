<?php

namespace App\Console\Commands;

use App\Services\OpenAIVectorService;
use Illuminate\Console\Command;

class TestVectorizationIntegrationCommand extends Command
{
    protected $signature = 'test:vectorization-integration';
    
    protected $description = 'Test the complete vectorization integration with all namespaces';

    protected OpenAIVectorService $vectorService;

    public function __construct(OpenAIVectorService $vectorService)
    {
        parent::__construct();
        $this->vectorService = $vectorService;
    }

    public function handle()
    {
        $this->info("🧪 Testing complete vectorization integration...");
        $this->newLine();

        // Test all namespaces
        $namespaces = [
            'lagento_context' => 'Contexte LagentO (FAQ, institutions, textes officiels)',
            'opportunites' => 'Opportunités entrepreneuriales (incubation, financement)',
            'user_diagnostics' => 'Diagnostics utilisateur (auto-vectorisés)',
            'conversation_summaries' => 'Résumés de conversations',
            'message_attachments' => 'Attachements de messages'
        ];

        $testQueries = [
            'financement startup' => ['lagento_context', 'opportunites'],
            'programmes incubation' => ['lagento_context', 'opportunites'],
            'diagnostic entrepreneurial' => ['user_diagnostics'],
            'texte officiel OHADA' => ['lagento_context'],
            'institutions Côte d\'Ivoire' => ['lagento_context']
        ];

        foreach ($namespaces as $namespace => $description) {
            $this->info("📦 Testing namespace: {$namespace}");
            $this->line("   Description: {$description}");
            
            try {
                $results = $this->vectorService->searchSimilar(
                    query: 'entreprise',
                    topK: 3,
                    filter: [],
                    namespace: $namespace
                );
                
                $count = count($results);
                if ($count > 0) {
                    $this->info("   ✅ Found {$count} vectors");
                    
                    // Show sample result
                    $sample = $results[0];
                    $type = $sample['metadata']['type'] ?? 'unknown';
                    $source = $sample['metadata']['source'] ?? 'unknown';
                    $this->line("   📄 Sample: Type={$type}, Source={$source}");
                } else {
                    $this->warn("   ⚠️  No vectors found (namespace may be empty)");
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Error: " . $e->getMessage());
            }
            
            $this->newLine();
        }

        $this->info("🔍 Testing cross-namespace queries...");
        $this->newLine();

        foreach ($testQueries as $query => $expectedNamespaces) {
            $this->info("Query: '{$query}'");
            $this->line("Expected namespaces: " . implode(', ', $expectedNamespaces));
            
            $allResults = [];
            
            foreach ($expectedNamespaces as $namespace) {
                try {
                    $results = $this->vectorService->searchSimilar(
                        query: $query,
                        topK: 2,
                        filter: [],
                        namespace: $namespace
                    );
                    
                    if (!empty($results)) {
                        $allResults[$namespace] = count($results);
                        $this->line("  ✅ {$namespace}: " . count($results) . " results");
                    } else {
                        $this->line("  ⚠️  {$namespace}: 0 results");
                    }
                } catch (\Exception $e) {
                    $this->line("  ❌ {$namespace}: Error - " . $e->getMessage());
                }
            }
            
            $totalResults = array_sum($allResults);
            $this->line("  📊 Total: {$totalResults} results across " . count($allResults) . " namespaces");
            $this->newLine();
        }

        // Test the AgentPrincipal integration
        $this->info("🤖 Testing AgentPrincipal integration...");
        
        try {
            $agent = new \App\Agents\AgentPrincipal();
            $testUserId = 'test-user-123';
            $testMessage = 'Je cherche des opportunités de financement pour ma startup';
            
            // This would normally call executeVectorSearch but we'll test the search directly
            $contextResults = $this->vectorService->searchSimilar(
                query: $testMessage,
                topK: 4,
                filter: [],
                namespace: 'lagento_context'
            );
            
            $opportunityResults = $this->vectorService->searchSimilar(
                query: $testMessage,
                topK: 2,
                filter: [],
                namespace: 'opportunites'
            );
            
            $totalAgentResults = count($contextResults) + count($opportunityResults);
            
            $this->info("  ✅ Agent would find {$totalAgentResults} results:");
            $this->line("     📚 Context: " . count($contextResults) . " results");
            $this->line("     🎯 Opportunities: " . count($opportunityResults) . " results");
            
            if ($totalAgentResults >= 4) {
                $this->info("  🎉 Integration successful - Agent has sufficient context!");
            } else {
                $this->warn("  ⚠️  Integration may need improvement - Limited results");
            }
            
        } catch (\Exception $e) {
            $this->error("  ❌ Agent integration error: " . $e->getMessage());
        }

        $this->newLine();
        $this->info("✅ Vectorization integration test completed!");
        
        return 0;
    }
}