<?php

namespace App\Console\Commands;

use App\Services\OpenAIVectorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestPineconeCommand extends Command
{
    protected $signature = 'test:pinecone 
                          {--namespace=opportunites : Namespace to test}
                          {--query=financement startup : Search query to test}';
    
    protected $description = 'Test Pinecone vector search functionality';

    protected OpenAIVectorService $vectorService;

    public function __construct(OpenAIVectorService $vectorService)
    {
        parent::__construct();
        $this->vectorService = $vectorService;
    }

    public function handle()
    {
        $namespace = $this->option('namespace');
        $query = $this->option('query');
        
        $this->info("🔍 Testing Pinecone search functionality...");
        $this->info("📦 Namespace: {$namespace}");
        $this->info("🔎 Query: '{$query}'");
        $this->newLine();

        try {
            // Test different search queries
            $queries = [
                $query,
                "programmes d'incubation",
                "financement pour startups",
                "Orange Corners",
                "MTN",
                "formation entrepreneuriale",
                "Côte d'Ivoire"
            ];

            foreach ($queries as $testQuery) {
                $this->info("🔍 Searching for: '{$testQuery}'");
                
                $results = $this->vectorService->searchSimilar(
                    query: $testQuery,
                    topK: 3,
                    filter: [],
                    namespace: $namespace
                );
                
                if (!empty($results)) {
                    $this->info("✅ Found " . count($results) . " results:");
                    
                    foreach ($results as $index => $result) {
                        $score = round($result['score'], 3);
                        $metadata = $result['metadata'] ?? [];
                        
                        $this->line("  📌 Result " . ($index + 1) . ":");
                        $this->line("     🎯 Score: {$score}");
                        $this->line("     🏷️  Type: " . ($metadata['type'] ?? 'Unknown'));
                        $this->line("     📄 Source: " . ($metadata['source'] ?? 'Unknown'));
                        
                        if (isset($metadata['content'])) {
                            $preview = mb_substr($metadata['content'], 0, 200) . '...';
                            $this->line("     📝 Content: {$preview}");
                        }
                        
                        // Try to extract institution from content
                        if (isset($metadata['content'])) {
                            if (preg_match('/INSTITUTION: ([^\n]+)/', $metadata['content'], $matches)) {
                                $this->line("     🏢 Institution: " . trim($matches[1]));
                            }
                            if (preg_match('/TITRE: ([^\n]+)/', $metadata['content'], $matches)) {
                                $this->line("     📋 Titre: " . trim($matches[1]));
                            }
                        }
                        
                        $this->newLine();
                    }
                } else {
                    $this->warn("⚠️  No results found for '{$testQuery}'");
                }
                
                $this->line("─────────────────────────────────────────────");
            }
            
            // Test namespace statistics
            $this->info("📊 Testing namespace statistics...");
            
            // Try a very broad search to see what's available
            $allResults = $this->vectorService->searchSimilar(
                query: "opportunité",
                topK: 10,
                filter: [],
                namespace: $namespace
            );
            
            $this->info("📈 Total vectors found with broad search: " . count($allResults));
            
            if (!empty($allResults)) {
                $institutions = [];
                $types = [];
                
                foreach ($allResults as $result) {
                    $content = $result['metadata']['content'] ?? '';
                    
                    // Extract institutions
                    if (preg_match('/INSTITUTION: ([^\n]+)/', $content, $matches)) {
                        $inst = trim($matches[1]);
                        $institutions[$inst] = ($institutions[$inst] ?? 0) + 1;
                    }
                    
                    // Extract types
                    if (preg_match('/TYPE: ([^\n]+)/', $content, $matches)) {
                        $type = trim($matches[1]);
                        $types[$type] = ($types[$type] ?? 0) + 1;
                    }
                }
                
                if (!empty($institutions)) {
                    $this->info("🏢 Institutions found:");
                    foreach (array_slice($institutions, 0, 10) as $inst => $count) {
                        $this->line("   • {$inst} ({$count})");
                    }
                }
                
                if (!empty($types)) {
                    $this->info("📋 Types found:");
                    foreach ($types as $type => $count) {
                        $this->line("   • {$type} ({$count})");
                    }
                }
            }
            
            $this->newLine();
            $this->info("✅ Pinecone test completed successfully!");
            
        } catch (\Exception $e) {
            $this->error("❌ Error during Pinecone test: " . $e->getMessage());
            
            Log::error('Pinecone test failed', [
                'error' => $e->getMessage(),
                'namespace' => $namespace,
                'query' => $query
            ]);
            
            return 1;
        }

        return 0;
    }
}