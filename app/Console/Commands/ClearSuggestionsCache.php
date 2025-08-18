<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AgentService;

class ClearSuggestionsCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suggestions:clear {user_id? : ID de l\'utilisateur (optionnel)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vider le cache des suggestions pour un utilisateur ou tous les utilisateurs';

    /**
     * Execute the console command.
     */
    public function handle(AgentService $agentService)
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            // Vider le cache pour un utilisateur spécifique
            $agentService->clearUserSuggestionsCache($userId);
            $this->info("Cache des suggestions vidé pour l'utilisateur: {$userId}");
        } else {
            // Vider tout le cache des suggestions
            $this->confirm('Êtes-vous sûr de vouloir vider tout le cache des suggestions ?') or exit;
            
            try {
                \Cache::tags(['suggestions'])->flush();
                $this->info('Tout le cache des suggestions a été vidé.');
            } catch (\Exception $e) {
                // Fallback si les tags ne sont pas supportés
                $pattern = 'suggestions:*';
                if (\Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                    $redis = \Cache::getStore()->getRedis();
                    $keys = $redis->keys($pattern);
                    if (!empty($keys)) {
                        $redis->del($keys);
                        $this->info('Cache des suggestions vidé (via pattern Redis).');
                    } else {
                        $this->info('Aucune clé de cache trouvée.');
                    }
                } else {
                    $this->warn('Pattern matching non supporté pour ce type de cache.');
                }
            }
        }
        
        return 0;
    }
}
