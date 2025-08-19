<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VoyageVectorService;
use App\Models\VectorMemory;
use Illuminate\Support\Facades\DB;

class VectorizeContextCommand extends Command
{
    protected $signature = 'vector:context 
                          {--chunk-size=25000 : Size of text chunks in characters}
                          {--clear : Clear existing context vectors}
                          {--resume : Resume from where we left off}
                          {--timeout=60 : HTTP timeout in seconds}';
    
    protected $description = 'Vectorize lagento_contexte.txt and store in database';

    private VoyageVectorService $vectorService;

    public function __construct(VoyageVectorService $vectorService)
    {
        parent::__construct();
        $this->vectorService = $vectorService;
    }

    public function handle()
    {
        $corpusFile = storage_path('../data/lagento_contexte.txt');
        
        if (!file_exists($corpusFile)) {
            $this->error("Corpus file not found: $corpusFile");
            return 1;
        }

        // Clear existing context vectors and obsolete types if requested
        if ($this->option('clear')) {
            $this->info('🗑️  Clearing obsolete vector types...');
            
            // Supprimer les anciens types fusionnés dans le corpus
            $obsoleteTypes = [
                'lagento_context',
                'actions_gouvernementales', 
                'institutions',
                'textes_officiels',
                'timeline_actions'
            ];
            
            foreach ($obsoleteTypes as $type) {
                $count = VectorMemory::where('memory_type', $type)->count();
                if ($count > 0) {
                    VectorMemory::where('memory_type', $type)->delete();
                    $this->info("   Deleted {$count} vectors of type '{$type}'");
                }
            }
        }

        $this->info('📖 Reading corpus...');
        $content = file_get_contents($corpusFile);
        $totalSize = strlen($content);
        
        $this->info("📊 Corpus size: " . number_format($totalSize) . " characters");

        // Chunk the content
        $chunkSize = $this->option('chunk-size');
        $chunks = $this->createChunks($content, $chunkSize);
        
        $this->info("📦 Created " . count($chunks) . " chunks");

        $bar = $this->output->createProgressBar(count($chunks));
        $bar->start();

        $processed = 0;
        $errors = 0;

        foreach ($chunks as $index => $chunk) {
            // Skip if resuming and chunk already exists
            if ($this->option('resume')) {
                $existing = VectorMemory::where('memory_type', 'lagento_context')
                    ->where('source_id', 'corpus_' . $index)
                    ->exists();
                
                if ($existing) {
                    $bar->advance();
                    $processed++;
                    continue;
                }
            }

            try {
                // Set HTTP timeout
                config(['http.timeout' => $this->option('timeout')]);
                
                // Generate embedding with smaller context to reduce request size
                $embeddings = $this->vectorService->embedWithContext([$chunk], 'LAGENTO CI');
                
                if (!empty($embeddings[0]['embedding'])) {
                    // Store in database
                    VectorMemory::create([
                        'memory_type' => 'lagento_context',
                        'source_id' => 'corpus_' . $index,
                        'chunk_content' => $chunk,
                        'embedding' => $embeddings[0]['embedding'],
                        'metadata' => [
                            'chunk_index' => $index,
                            'chunk_size' => strlen($chunk),
                            'total_chunks' => count($chunks),
                            'source_file' => 'lagento_contexte.txt'
                        ]
                    ]);
                    
                    $processed++;
                } else {
                    $errors++;
                }

            } catch (\Exception $e) {
                $this->error("\n❌ Error processing chunk $index: " . $e->getMessage());
                $errors++;
                
                // Wait longer after timeout errors
                if (strpos($e->getMessage(), 'timeout') !== false) {
                    $this->info("⏳ Waiting 5 seconds after timeout...");
                    sleep(5);
                }
            }

            $bar->advance();
            
            // Adaptive delay based on success/failure
            usleep($errors > $processed ? 500000 : 200000); // 0.5s if more errors, 0.2s otherwise
        }

        $bar->finish();
        
        $this->newLine(2);
        $this->info("✅ Vectorization completed!");
        $this->info("📊 Processed: $processed chunks");
        $this->info("❌ Errors: $errors chunks");
        
        // Show stats
        $totalVectors = VectorMemory::where('memory_type', 'lagento_context')->count();
        $this->info("🔢 Total context vectors in DB: $totalVectors");

        return 0;
    }

    private function createChunks(string $content, int $chunkSize): array
    {
        $chunks = [];
        
        // Split by major sections first (dividers with ===)
        $sections = preg_split('/={50,}/', $content);
        
        foreach ($sections as $section) {
            if (strlen(trim($section)) < 100) continue;
            
            // If section is small enough, use as single chunk
            if (strlen($section) <= $chunkSize) {
                $chunks[] = trim($section);
            } else {
                // Split large sections by file dividers (###)
                $subSections = preg_split('/#{30,}/', $section);
                
                foreach ($subSections as $subSection) {
                    if (strlen(trim($subSection)) < 100) continue;
                    
                    if (strlen($subSection) <= $chunkSize) {
                        $chunks[] = trim($subSection);
                    } else {
                        // Final split by character count with sentence boundaries
                        $chunks = array_merge($chunks, $this->splitBySize($subSection, $chunkSize));
                    }
                }
            }
        }
        
        return array_filter($chunks, fn($chunk) => strlen(trim($chunk)) > 100);
    }

    private function splitBySize(string $text, int $chunkSize): array
    {
        $chunks = [];
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        $currentChunk = '';
        
        foreach ($sentences as $sentence) {
            $testChunk = trim($currentChunk . ' ' . $sentence);
            
            if (strlen($testChunk) > $chunkSize && !empty($currentChunk)) {
                $chunks[] = trim($currentChunk);
                $currentChunk = $sentence;
            } else {
                $currentChunk = $testChunk;
            }
        }
        
        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }
        
        return $chunks;
    }
}