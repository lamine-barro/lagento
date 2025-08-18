<?php

namespace App\Console\Commands;

use App\Services\MemoryManagerService;
use App\Models\Opportunite;
use App\Models\TexteOfficiel;
use App\Models\Institution;
use App\Models\Projet;
use App\Models\UserAnalytics;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IndexVectorMemories extends Command
{
    protected $signature = 'vector:index {--type=all : Type of memory to index (all|opportunites|institutions|textes|projects|analytics|static)}';
    
    protected $description = 'Index memories into vector database for semantic search';

    protected MemoryManagerService $memoryManager;

    public function __construct(MemoryManagerService $memoryManager)
    {
        parent::__construct();
        $this->memoryManager = $memoryManager;
    }

    public function handle()
    {
        $type = $this->option('type');
        
        $this->info("Starting vector indexing for: {$type}");
        
        try {
            switch ($type) {
                case 'all':
                    $this->indexAll();
                    break;
                case 'opportunites':
                    $this->indexOpportunites();
                    break;
                case 'institutions':
                    $this->indexInstitutions();
                    break;
                case 'textes':
                    $this->indexTextesOfficiels();
                    break;
                case 'projects':
                    $this->indexProjects();
                    break;
                case 'analytics':
                    $this->indexAnalytics();
                    break;
                case 'static':
                    $this->indexStaticMemories();
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

    private function indexAll()
    {
        $this->indexStaticMemories();
        $this->indexOpportunites();
        $this->indexInstitutions();
        $this->indexTextesOfficiels();
        $this->indexProjects();
        $this->indexAnalytics();
    }

    private function indexOpportunites()
    {
        $this->info("Indexing opportunités...");
        
        $count = 0;
        Opportunite::chunk(50, function ($opportunites) use (&$count) {
            foreach ($opportunites as $opportunite) {
                $this->memoryManager->indexMemory('opportunite', $opportunite);
                $count++;
                
                if ($count % 10 == 0) {
                    $this->info("Indexed {$count} opportunités...");
                }
            }
        });
        
        $this->info("Indexed {$count} opportunités total");
    }

    private function indexInstitutions()
    {
        $this->info("Indexing institutions...");
        
        $count = 0;
        Institution::chunk(50, function ($institutions) use (&$count) {
            foreach ($institutions as $institution) {
                $this->memoryManager->indexMemory('institution', $institution);
                $count++;
                
                if ($count % 10 == 0) {
                    $this->info("Indexed {$count} institutions...");
                }
            }
        });
        
        $this->info("Indexed {$count} institutions total");
    }

    private function indexTextesOfficiels()
    {
        $this->info("Indexing textes officiels...");
        
        $count = 0;
        TexteOfficiel::chunk(20, function ($textes) use (&$count) {
            foreach ($textes as $texte) {
                $this->memoryManager->indexMemory('texte_officiel', $texte);
                $count++;
                
                if ($count % 5 == 0) {
                    $this->info("Indexed {$count} textes officiels...");
                }
            }
        });
        
        $this->info("Indexed {$count} textes officiels total");
    }

    private function indexProjects()
    {
        $this->info("Indexing user projects...");
        
        $count = 0;
        Projet::chunk(50, function ($projets) use (&$count) {
            foreach ($projets as $projet) {
                $this->memoryManager->indexMemory('user_project', $projet);
                $count++;
                
                if ($count % 10 == 0) {
                    $this->info("Indexed {$count} projects...");
                }
            }
        });
        
        $this->info("Indexed {$count} projects total");
    }

    private function indexAnalytics()
    {
        $this->info("Indexing user analytics...");
        
        $count = 0;
        UserAnalytics::chunk(50, function ($analytics) use (&$count) {
            foreach ($analytics as $analytic) {
                $this->memoryManager->indexMemory('user_analytics', $analytic);
                $count++;
                
                if ($count % 10 == 0) {
                    $this->info("Indexed {$count} analytics...");
                }
            }
        });
        
        $this->info("Indexed {$count} analytics total");
    }

    private function indexStaticMemories()
    {
        $this->info("Indexing static memories...");
        
        // Index timeline and presentation
        $this->memoryManager->indexMemory('timeline_gov', null);
        $this->memoryManager->indexMemory('presentation', null);
        
        // Bulk index from CSV files
        $this->memoryManager->bulkIndexFromCSV();
        
        $this->info("Static memories indexed");
    }
}