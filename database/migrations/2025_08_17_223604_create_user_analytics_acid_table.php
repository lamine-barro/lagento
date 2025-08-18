<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing table if exists
        Schema::dropIfExists('user_analytics');
        
        Schema::create('user_analytics', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->uuid('projet_id')->nullable();
            $table->foreign('projet_id')->references('id')->on('projets')->nullOnDelete();
            
            // Métadonnées générales
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable(); // Pour données flexibles et historique
            
            // 1. PROFIL ENTREPRENEUR - Normalisé
            $table->enum('niveau_global', ['débutant', 'confirmé', 'expert'])->nullable();
            $table->tinyInteger('score_potentiel')->unsigned()->nullable(); // 0-100
            $table->json('forces')->nullable(); // [{domaine, description}]
            $table->json('axes_progression')->nullable(); // [{domaine, action_suggeree, impact}]
            $table->json('besoins_formation')->nullable(); // [string]
            $table->enum('profil_type', ['innovateur', 'gestionnaire', 'commercial', 'artisan', 'commerçant'])->nullable();
            
            // 2. DIAGNOSTIC PROJET - Normalisé  
            $table->tinyInteger('score_sante')->unsigned()->nullable(); // 0-100
            $table->enum('niveau_maturite', ['idée', 'lancement', 'croissance', 'expansion'])->nullable();
            $table->enum('viabilite', ['très_forte', 'forte', 'moyenne', 'à_renforcer'])->nullable();
            
            // Indicateurs clés structurés
            $table->enum('statut_formalisation', ['ok', 'à_faire', 'en_cours'])->nullable();
            $table->json('actions_formalisation')->nullable(); // [string]
            $table->enum('urgence_formalisation', ['immédiate', 'sous_30j', 'sous_90j'])->nullable();
            
            $table->enum('statut_finance', ['sain', 'stable', 'fragile'])->nullable();
            $table->boolean('besoin_financement')->nullable();
            $table->string('montant_suggere')->nullable();
            
            $table->boolean('equipe_complete')->nullable();
            $table->json('besoins_equipe')->nullable(); // [string]
            
            $table->enum('position_marche', ['leader', 'bien_placé', 'nouveau', 'difficile'])->nullable();
            $table->enum('potentiel_marche', ['très_élevé', 'élevé', 'moyen', 'faible'])->nullable();
            
            $table->json('prochaines_etapes')->nullable(); // [{priorite, action, delai, ressource}]
            
            // 3. OPPORTUNITÉS MATCHÉES - Normalisé
            $table->tinyInteger('nombre_opportunites')->unsigned()->default(0);
            $table->json('top_opportunites')->nullable(); // [{id, titre, type, score_compatibilite, urgence, etc}]
            $table->tinyInteger('count_financement')->unsigned()->default(0);
            $table->tinyInteger('count_formation')->unsigned()->default(0);
            $table->tinyInteger('count_marche')->unsigned()->default(0);
            $table->tinyInteger('count_accompagnement')->unsigned()->default(0);
            
            // 4. INSIGHTS MARCHÉ - Normalisé
            $table->string('taille_marche_local')->nullable(); // "X FCFA"
            $table->string('taille_marche_potentiel')->nullable(); // "X FCFA"
            $table->string('croissance_marche')->nullable(); // "X %"
            $table->text('position_concurrentielle')->nullable();
            $table->json('principaux_concurrents')->nullable(); // [string]
            $table->text('avantage_cle')->nullable();
            $table->json('tendances')->nullable(); // [{tendance, impact_pour_vous}]
            $table->json('zones_opportunites')->nullable(); // [{region, potentiel, raison}]
            $table->text('conseil_strategique')->nullable();
            
            // 5. RÉGLEMENTATIONS - Normalisé
            $table->enum('conformite_globale', ['conforme', 'partiel', 'non_conforme'])->nullable();
            $table->json('urgent_regulations')->nullable(); // [{obligation, deadline, cout, ou_faire, contact}]
            $table->json('a_prevoir_regulations')->nullable(); // [{obligation, echeance, description}]
            $table->json('avantages_disponibles')->nullable(); // [{type, description, conditions}]
            
            // 6. PARTENAIRES SUGGÉRÉS - Normalisé
            $table->tinyInteger('nombre_partenaires')->unsigned()->default(0);
            $table->json('top_partenaires')->nullable(); // [{id, nom_projet, secteur, type_synergie, score_pertinence, etc}]
            $table->tinyInteger('clients_potentiels')->unsigned()->default(0);
            $table->tinyInteger('fournisseurs_potentiels')->unsigned()->default(0);
            $table->tinyInteger('partenaires_complementaires')->unsigned()->default(0);
            
            // 7. RÉSUMÉ EXÉCUTIF - Normalisé
            $table->text('message_principal')->nullable();
            $table->json('trois_actions_cles')->nullable(); // [string, string, string]
            $table->text('opportunite_du_mois')->nullable();
            $table->text('alerte_importante')->nullable();
            $table->tinyInteger('score_progression')->unsigned()->nullable(); // 0-100
            
            $table->timestamps();
            
            // Index pour performance
            $table->index(['user_id', 'generated_at']);
            $table->index(['user_id', 'expires_at']);
            $table->index(['projet_id']);
            $table->index(['niveau_global', 'score_potentiel']);
            $table->index(['niveau_maturite', 'score_sante']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_analytics');
    }
};