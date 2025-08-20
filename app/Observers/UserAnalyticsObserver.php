<?php

namespace App\Observers;

use App\Models\UserAnalytics;
use App\Services\AutoVectorizationService;
use Illuminate\Support\Facades\Log;

class UserAnalyticsObserver
{
    private AutoVectorizationService $autoVectorService;

    public function __construct(AutoVectorizationService $autoVectorService)
    {
        $this->autoVectorService = $autoVectorService;
    }

    /**
     * Handle the UserAnalytics "created" event.
     */
    public function created(UserAnalytics $analytics): void
    {
        try {
            $this->autoVectorService->vectorizeDiagnostic($analytics);
            Log::info('User analytics automatically vectorized on creation', [
                'id' => $analytics->id,
                'user_id' => $analytics->user_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to vectorize user analytics on creation', [
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
                
                $this->autoVectorService->vectorizeDiagnostic($analytics);
                Log::info('User analytics automatically re-vectorized on update', [
                    'id' => $analytics->id,
                    'user_id' => $analytics->user_id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to re-vectorize user analytics on update', [
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
            // Note: Vector deletion from Pinecone could be implemented here if needed
            // For now, we just log the deletion
            Log::info('User analytics deleted (vector removal from Pinecone not implemented)', [
                'id' => $analytics->id,
                'user_id' => $analytics->user_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to handle user analytics deletion', [
                'id' => $analytics->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}