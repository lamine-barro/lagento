<?php

namespace App\Observers;

use App\Models\Projet;
use Illuminate\Support\Facades\Log;

class ProjetObserver
{

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
            // Note: Vector deletion from Pinecone could be implemented here if needed
            // For now, we just log the deletion since projects aren't auto-vectorized
            Log::info('Project deleted (no vectors to remove)', ['id' => $projet->id]);
        } catch (\Exception $e) {
            Log::error('Failed to handle project deletion', [
                'id' => $projet->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}