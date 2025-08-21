<?php

namespace App\Console\Commands;

use App\Services\OpenAIVectorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class OptimizedVectorizationCommand extends Command
{
    protected $signature = 'vectorize:optimize 
                          {--reset : Reset all vectors before processing}
                          {--chunk-size=1000 : Optimized chunk size (800-1200 recommended)}
                          {--overlap=20 : Overlap percentage (20% recommended)}
                          {--namespace=lagento_optimized : Pinecone namespace}';
    
    protected $description = 'Optimized vectorization with smaller chunks and better performance';

    protected OpenAIVectorService $vectorService;

    public function __construct(OpenAIVectorService $vectorService)
    {
        parent::__construct();
        $this->vectorService = $vectorService;
    }

    public function handle()
    {
        $chunkSize = (int) $this->option('chunk-size');
        $overlapPercentage = (int) $this->option('overlap');
        $namespace = $this->option('namespace');
        $reset = $this->option('reset');
        
        // Validate chunk size
        if ($chunkSize < 500 || $chunkSize > 1500) {
            $this->error("❌ Chunk size should be between 500-1500 characters for optimal performance");
            return 1;
        }

        $this->info("🚀 Starting OPTIMIZED vectorization strategy...");
        $this->info("📊 Performance optimizations:");
        $this->info("   ✂️  Chunk size: {$chunkSize} chars (vs 4000 before)");
        $this->info("   🔄 Overlap: {$overlapPercentage}% (vs 10% before)");
        $this->info("   🎯 Target: 3-4x faster retrieval");
        
        try {
            // Reset vectors if requested
            if ($reset) {
                $contextNamespace = $namespace . '_context';
                $opportunitiesNamespace = $namespace . '_opportunities';
                
                $this->warn("🗑️  RESETTING vectors in SEPARATE namespaces...");
                $this->info("   📄 Context namespace: {$contextNamespace}");
                $this->info("   💼 Opportunities namespace: {$opportunitiesNamespace}");
                
                $deletedContext = $this->vectorService->deleteVectors(null, $contextNamespace);
                $deletedOpps = $this->vectorService->deleteVectors(null, $opportunitiesNamespace);
                
                $this->info($deletedContext ? "✅ Context vectors reset" : "⚠️  No context vectors found");
                $this->info($deletedOpps ? "✅ Opportunities vectors reset" : "⚠️  No opportunities vectors found");
            }

            // Process main context file (separate namespace)
            $contextNamespace = $namespace . '_context';
            $this->processLagentoContext($chunkSize, $overlapPercentage, $contextNamespace);
            
            // Process opportunities (separate namespace)
            $opportunitiesNamespace = $namespace . '_opportunities';
            $this->processOpportunities($chunkSize, $overlapPercentage, $opportunitiesNamespace);
            
            // Performance test on both namespaces
            $this->performanceTest($contextNamespace, $opportunitiesNamespace);
            
        } catch (\Exception $e) {
            $this->error("❌ Optimization failed: " . $e->getMessage());
            Log::error('Optimized vectorization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        $this->info("🎉 Optimized vectorization completed!");
        return 0;
    }

    private function processLagentoContext(int $chunkSize, int $overlapPercentage, string $namespace)
    {
        $filePath = base_path('data/lagento_contexte.txt');
        
        if (!file_exists($filePath)) {
            $this->warn("⚠️  LagentO context file not found: {$filePath}");
            return;
        }

        $this->info("📄 Processing LagentO context (3.3MB)...");
        $content = file_get_contents($filePath);
        $fileSize = strlen($content);
        
        // Enhanced semantic chunking
        $this->info("🧠 Applying semantic chunking...");
        $sections = $this->extractSemanticSections($content);
        
        $totalChunks = 0;
        $bar = $this->output->createProgressBar(count($sections));
        $bar->start();
        
        foreach ($sections as $sectionName => $sectionContent) {
            $success = $this->vectorService->processAndStore(
                $sectionContent,
                "lagento_context_{$sectionName}",
                [
                    'type' => 'lagento_context',
                    'section' => $sectionName,
                    'source' => 'lagento_contexte.txt',
                    'version' => date('Y-m-d_H-i-s'),
                    'chunk_strategy' => 'optimized_semantic',
                    'chunk_size' => $chunkSize,
                    'overlap_percent' => $overlapPercentage
                ],
                $namespace,
                $chunkSize,
                $overlapPercentage / 100
            );
            
            if ($success) {
                $chunks = $this->vectorService->chunkText($sectionContent, $chunkSize, $overlapPercentage / 100);
                $totalChunks += count($chunks);
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("✅ LagentO context: {$totalChunks} optimized chunks created");
    }

    private function processOpportunities(int $chunkSize, int $overlapPercentage, string $namespace)
    {
        $filePath = base_path('data/opportunites.csv');
        
        if (!file_exists($filePath)) {
            $this->warn("⚠️  Opportunities file not found: {$filePath}");
            return;
        }

        $this->info("💼 Processing opportunities data...");
        
        // Convert CSV to optimized text format
        $csvData = array_map('str_getcsv', file($filePath));
        $headers = array_shift($csvData);
        
        $opportunitiesText = $this->formatOpportunitiesForVectorization($csvData, $headers);
        
        $success = $this->vectorService->processAndStore(
            $opportunitiesText,
            'lagento_opportunities_optimized',
            [
                'type' => 'opportunities',
                'source' => 'opportunites_final.csv',
                'count' => count($csvData),
                'version' => date('Y-m-d_H-i-s'),
                'chunk_strategy' => 'optimized',
                'chunk_size' => $chunkSize,
                'overlap_percent' => $overlapPercentage
            ],
            $namespace,
            $chunkSize,
            $overlapPercentage / 100
        );

        if ($success) {
            $chunks = $this->vectorService->chunkText($opportunitiesText, $chunkSize, $overlapPercentage / 100);
            $this->info("✅ Opportunities: " . count($chunks) . " optimized chunks created");
        }
    }

    private function extractSemanticSections(string $content): array
    {
        $sections = [];
        
        // Pattern for section detection
        $patterns = [
            'institutions' => '/####################################################################################################\s*################################## FICHIER \d+\/\d+: institutions\.csv ##################################\s*####################################################################################################(.*?)(?=####################################################################################################|$)/s',
            'procedures' => '/####################################################################################################\s*################################## FICHIER \d+\/\d+: .*procedures.*(.*?)(?=####################################################################################################|$)/s',
            'faq' => '/####################################################################################################\s*################################## FICHIER \d+\/\d+: .*faq.*(.*?)(?=####################################################################################################|$)/s',
            'presentations' => '/####################################################################################################\s*################################## FICHIER \d+\/\d+: .*presentation.*(.*?)(?=####################################################################################################|$)/s',
        ];
        
        foreach ($patterns as $sectionName => $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $sections[$sectionName] = trim($matches[1]);
            }
        }
        
        // Fallback: split by file markers if semantic extraction fails
        if (empty($sections)) {
            $fileSections = preg_split('/####################################################################################################/', $content);
            foreach ($fileSections as $i => $section) {
                if (strlen(trim($section)) > 100) {
                    $sections["section_$i"] = trim($section);
                }
            }
        }
        
        return array_filter($sections, function($content) {
            return strlen($content) > 50; // Minimum content length
        });
    }

    private function formatOpportunitiesForVectorization(array $data, array $headers): string
    {
        $formatted = "=== OPPORTUNITÉS ENTREPRENEURIALES CÔTE D'IVOIRE ===\n\n";
        
        foreach ($data as $row) {
            $opportunity = array_combine($headers, $row);
            
            $formatted .= "--- OPPORTUNITÉ ---\n";
            $formatted .= "Institution: " . ($opportunity['institution_clean'] ?? 'N/A') . "\n";
            $formatted .= "Type: " . ($opportunity['institution_type'] ?? 'N/A') . "\n";
            $formatted .= "Statut: " . ($opportunity['statut'] ?? 'N/A') . "\n";
            $formatted .= "Titre: " . ($opportunity['titre'] ?? 'N/A') . "\n";
            $formatted .= "Description: " . ($opportunity['description'] ?? 'N/A') . "\n";
            $formatted .= "Secteurs: " . ($opportunity['secteurs'] ?? 'N/A') . "\n";
            $formatted .= "Pays: " . ($opportunity['pays'] ?? 'N/A') . "\n";
            $formatted .= "Régions ciblées: " . ($opportunity['regions_ciblees'] ?? 'N/A') . "\n";
            $formatted .= "Critères éligibilité: " . ($opportunity['criteres_eligibilite_enrichis'] ?? 'N/A') . "\n";
            $formatted .= "Contact: " . ($opportunity['contact_email_enrichi'] ?? 'N/A') . "\n";
            $formatted .= "Lien: " . ($opportunity['lien_externe'] ?? 'N/A') . "\n";
            $formatted .= "\n";
        }
        
        return $formatted;
    }

    private function performanceTest(string $contextNamespace, string $opportunitiesNamespace)
    {
        $this->info("🔍 Running performance test on SEPARATE namespaces...");
        
        // Test context queries
        $contextQueries = [
            "Qu'est-ce que LagentO ?",
            "Comment créer une entreprise en Côte d'Ivoire ?",
            "Institutions d'accompagnement à Abidjan"
        ];
        
        // Test opportunities queries
        $opportunitiesQueries = [
            "Quelles sont les opportunités de financement ?",
            "Programme d'incubation startups",
            "Orange Corners CI"
        ];
        
        $this->info("📄 Testing CONTEXT namespace: {$contextNamespace}");
        $contextStats = $this->runNamespaceTest($contextQueries, $contextNamespace);
        
        $this->info("💼 Testing OPPORTUNITIES namespace: {$opportunitiesNamespace}");
        $oppsStats = $this->runNamespaceTest($opportunitiesQueries, $opportunitiesNamespace);
        
        // Overall performance summary
        $this->info("📊 OPTIMIZED PERFORMANCE SUMMARY:");
        $this->info("   📄 Context - Avg: " . round($contextStats['avgTime'], 1) . "ms, Success: " . round($contextStats['successRate'], 1) . "%");
        $this->info("   💼 Opportunities - Avg: " . round($oppsStats['avgTime'], 1) . "ms, Success: " . round($oppsStats['successRate'], 1) . "%");
        
        $overallAvg = ($contextStats['avgTime'] + $oppsStats['avgTime']) / 2;
        $overallSuccess = ($contextStats['successRate'] + $oppsStats['successRate']) / 2;
        
        $this->info("   🎯 OVERALL - Avg: " . round($overallAvg, 1) . "ms, Success: " . round($overallSuccess, 1) . "%");
        
        if ($overallAvg < 500) {
            $this->info("🚀 EXCELLENT: Performance target achieved!");
        } elseif ($overallAvg < 1000) {
            $this->info("✅ GOOD: Performance acceptable");
        } else {
            $this->warn("⚠️  SLOW: Consider further optimization");
        }
    }
    
    private function runNamespaceTest(array $queries, string $namespace): array
    {
        $totalTime = 0;
        $successCount = 0;
        
        foreach ($queries as $query) {
            $start = microtime(true);
            
            $results = $this->vectorService->searchSimilar(
                query: $query,
                topK: 3,
                namespace: $namespace
            );
            
            $time = (microtime(true) - $start) * 1000; // Convert to ms
            $totalTime += $time;
            
            if (!empty($results)) {
                $successCount++;
                $this->line("  ✅ '{$query}' - " . round($time, 1) . "ms - " . count($results) . " results");
            } else {
                $this->line("  ❌ '{$query}' - " . round($time, 1) . "ms - No results");
            }
        }
        
        return [
            'avgTime' => $totalTime / count($queries),
            'successRate' => ($successCount / count($queries)) * 100,
            'totalQueries' => count($queries),
            'successCount' => $successCount
        ];
    }
}