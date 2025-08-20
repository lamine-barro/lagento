<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutoVectorizationService;
use App\Models\Projet;
use App\Models\UserAnalytics;
use Illuminate\Support\Facades\Log;

class IndexUserDataCommand extends Command
{
    protected $signature = 'vector:index-user-data {--type=all : Type to index (projects|analytics|all)}';
    protected $description = 'Index user projects and analytics into vector memory for AI agent access';

    private AutoVectorizationService $autoVectorService;

    public function __construct(AutoVectorizationService $autoVectorService)
    {
        parent::__construct();
        $this->autoVectorService = $autoVectorService;
    }

    public function handle()
    {
        $type = $this->option('type');
        
        $this->info('🚀 Starting user data indexation...');
        
        if ($type === 'all' || $type === 'analytics') {
            $this->indexAnalytics();
        }
        
        $this->info('✅ Indexation complete!');
        return 0;
    }
    
    
    private function indexAnalytics(): void
    {
        $this->info('Indexing user analytics...');
        
        $analytics = UserAnalytics::all();
        $bar = $this->output->createProgressBar($analytics->count());
        $bar->start();
        
        $successful = 0;
        $failed = 0;
        
        foreach ($analytics as $analytic) {
            try {
                $this->autoVectorService->vectorizeDiagnostic($analytic);
                $successful++;
                
                Log::info('Analytics indexed successfully', [
                    'analytics_id' => $analytic->id,
                    'user_id' => $analytic->user_id
                ]);
            } catch (\Exception $e) {
                $failed++;
                $this->error("\n❌ Failed to index analytics {$analytic->id}: " . $e->getMessage());
                
                Log::error('Failed to index analytics', [
                    'analytics_id' => $analytic->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->info("✅ Analytics indexed: $successful successful, $failed failed");
    }
}