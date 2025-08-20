<?php

namespace App\Console\Commands;

use App\Services\OpenAIVectorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VectorizeLagentOContextCommand extends Command
{
    protected $signature = 'vectorize:lagento-context 
                          {--file=data/lagento_contexte.txt : Path to context file}
                          {--chunk-size=4000 : Chunk size for processing (large chunks for fewer segments)}
                          {--overlap=10 : Overlap percentage between chunks}
                          {--namespace=lagento_context : Pinecone namespace}';
    
    protected $description = 'Vectorize LagentO context file using OpenAI embeddings and store in Pinecone';

    protected OpenAIVectorService $vectorService;

    public function __construct(OpenAIVectorService $vectorService)
    {
        parent::__construct();
        $this->vectorService = $vectorService;
    }

    public function handle()
    {
        $filePath = base_path($this->option('file'));
        $chunkSize = (int) $this->option('chunk-size');
        $overlapPercentage = (int) $this->option('overlap');
        $namespace = $this->option('namespace');
        
        if (!file_exists($filePath)) {
            $this->error("Context file not found: {$filePath}");
            return 1;
        }

        $this->info("🚀 Starting vectorization of LagentO context...");
        $this->info("📁 File: {$filePath}");
        $this->info("📦 Namespace: {$namespace}");
        $this->info("✂️  Chunk size: {$chunkSize} characters");
        $this->info("🔄 Overlap: {$overlapPercentage}%");

        try {
            // Read the context file
            $content = file_get_contents($filePath);
            $fileSize = strlen($content);
            $this->info("📄 File size: " . number_format($fileSize) . " characters");

            // Delete existing vectors in namespace
            $this->warn("🗑️  Deleting existing vectors in namespace '{$namespace}'...");
            $deleted = $this->vectorService->deleteVectors(null, $namespace);
            
            if ($deleted) {
                $this->info("✅ Existing vectors deleted successfully");
            } else {
                $this->warn("⚠️  Failed to delete existing vectors or none existed");
            }

            // Process and store the content
            $this->info("🔄 Processing and vectorizing content...");
            
            $success = $this->vectorService->processAndStore(
                $content,
                'lagento_context_main',
                [
                    'type' => 'lagento_context',
                    'source' => 'lagento_contexte.txt',
                    'version' => date('Y-m-d_H-i-s'),
                    'file_size' => $fileSize,
                    'description' => 'LagentO complete context: FAQ, 100+ institutions, 20+ official texts, government presentations'
                ],
                $namespace,
                $chunkSize,
                $overlapPercentage / 100
            );

            if ($success) {
                $this->info("✅ Vectorization completed successfully!");
                $this->info("🎯 Content has been chunked, embedded, and stored in Pinecone");
                
                // Test search to verify
                $this->info("🔍 Testing search functionality...");
                $results = $this->vectorService->searchSimilar(
                    query: "Qu'est-ce que LagentO ?",
                    topK: 3,
                    namespace: $namespace
                );
                
                if (!empty($results)) {
                    $this->info("✅ Search test successful - found " . count($results) . " relevant chunks");
                    foreach ($results as $result) {
                        $score = round($result['score'], 3);
                        $content = substr($result['metadata']['content'] ?? '', 0, 100) . '...';
                        $this->line("  📌 Score: {$score} - {$content}");
                    }
                } else {
                    $this->warn("⚠️  Search test returned no results");
                }
                
            } else {
                $this->error("❌ Vectorization failed!");
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("❌ Error during vectorization: " . $e->getMessage());
            Log::error('Context vectorization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        $this->info("🎉 Vectorization process completed!");
        return 0;
    }
}