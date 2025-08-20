<?php

namespace App\Console\Commands;

use App\Services\AutoVectorizationService;
use App\Models\Projet;
use App\Models\UserAnalytics;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IndexVectorMemories extends Command
{
    protected $signature = 'vector:index {--type=all : Type of memory to index (all|projects|analytics|static)}';
    
    protected $description = 'Index memories into vector database for semantic search';

    protected AutoVectorizationService $autoVectorService;

    public function __construct(AutoVectorizationService $autoVectorService)
    {
        parent::__construct();
        $this->autoVectorService = $autoVectorService;
    }

    public function handle()
    {
        $type = $this->option('type');
        
        $this->info("Starting vector indexing for: {$type}");
        
        try {
            switch ($type) {
                case 'all':
                case 'analytics':
                    $this->indexAnalytics();
                    break;
                default:
                    $this->error("Unknown type: {$type}");
                    return 1;
            }
            
            $this->info("Vector indexing completed successfully!");
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Vector indexing failed: " . $e->getMessage());
            Log::error('Vector indexing command failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }


    private function indexAnalytics()
    {
        $this->info("Indexing user analytics...");
        
        $count = 0;
        UserAnalytics::chunk(50, function ($analytics) use (&$count) {
            foreach ($analytics as $analytic) {
                $this->autoVectorService->vectorizeDiagnostic($analytic);
                $count++;
                
                if ($count % 10 == 0) {
                    $this->info("Indexed {$count} analytics...");
                }
            }
        });
        
        $this->info("Indexed {$count} analytics total");
    }

}