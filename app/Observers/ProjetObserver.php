<?php

namespace App\Observers;

use App\Models\Projet;
use App\Services\MemoryManagerService;
use Illuminate\Support\Facades\Log;

class ProjetObserver
{
    private MemoryManagerService $memoryManager;

    public function __construct(MemoryManagerService $memoryManager)
    {
        $this->memoryManager = $memoryManager;
    }

    /**
     * Handle the Projet "created" event.
     */
    public function saved(Projet $projet): void
    {
        try {
            if ($projet->id) {
                $this->memoryManager->indexMemory('user_project', $projet);
                Log::info('Project automatically indexed after save', ['id' => $projet->id]);
            } else {
                Log::warning('Cannot index project: ID not available', ['projet' => $projet->toArray()]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to index project after save', [
                'id' => $projet->id,
                'error' => $e->getMessage()
            ]);
        }
    }


    /**
     * Handle the Projet "deleted" event.
     */
    public function deleted(Projet $projet): void
    {
        try {
            // Remove from vector memory
            \DB::table('vector_memories')
                ->where('memory_type', 'user_project')
                ->where('source_id', $projet->id)
                ->delete();
            
            Log::info('Project vectors removed on deletion', ['id' => $projet->id]);
        } catch (\Exception $e) {
            Log::error('Failed to remove project vectors on deletion', [
                'id' => $projet->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}