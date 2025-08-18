<?php

namespace App\Observers;

use App\Models\UserAnalytics;
use App\Services\MemoryManagerService;
use Illuminate\Support\Facades\Log;

class UserAnalyticsObserver
{
    private MemoryManagerService $memoryManager;

    public function __construct(MemoryManagerService $memoryManager)
    {
        $this->memoryManager = $memoryManager;
    }

    /**
     * Handle the UserAnalytics "created" event.
     */
    public function created(UserAnalytics $analytics): void
    {
        try {
            $this->memoryManager->indexMemory('user_analytics', $analytics);
            Log::info('User analytics automatically indexed on creation', [
                'id' => $analytics->id,
                'user_id' => $analytics->user_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to index user analytics on creation', [
                'id' => $analytics->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the UserAnalytics "updated" event.
     */
    public function updated(UserAnalytics $analytics): void
    {
        try {
            // Only re-index if significant fields changed
            if ($analytics->wasChanged(['entrepreneur_profile', 'score_sante', 'niveau_maturite', 
                'message_principal', 'nombre_opportunites', 'position_marche'])) {
                
                $this->memoryManager->indexMemory('user_analytics', $analytics);
                Log::info('User analytics automatically re-indexed on update', [
                    'id' => $analytics->id,
                    'user_id' => $analytics->user_id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to re-index user analytics on update', [
                'id' => $analytics->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the UserAnalytics "deleted" event.
     */
    public function deleted(UserAnalytics $analytics): void
    {
        try {
            // Remove from vector memory
            \DB::table('vector_memories')
                ->where('memory_type', 'user_analytics')
                ->where('source_id', $analytics->id)
                ->delete();
            
            Log::info('User analytics vectors removed on deletion', [
                'id' => $analytics->id,
                'user_id' => $analytics->user_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to remove user analytics vectors on deletion', [
                'id' => $analytics->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}