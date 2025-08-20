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
        // Auto-indexation disabled - project data will be used directly in context
        // instead of being vectorized
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