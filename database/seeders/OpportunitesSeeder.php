<?php

namespace Database\Seeders;

use App\Models\Opportunite;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OpportunitesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('../data/opportunites.csv');
        
        if (!file_exists($csvFile)) {
            Log::error("Le fichier CSV n'existe pas : {$csvFile}");
            echo "Erreur : Le fichier CSV n'existe pas à l'emplacement {$csvFile}\n";
            return;
        }

        // Vider la table avant d'importer
        Opportunite::truncate();

        $handle = fopen($csvFile, 'r');
        
        if (!$handle) {
            Log::error("Impossible d'ouvrir le fichier CSV");
            echo "Erreur : Impossible d'ouvrir le fichier CSV\n";
            return;
        }

        // Lire l'en-tête
        $headers = fgetcsv($handle, 0, ',');
        
        if (!$headers) {
            Log::error("Impossible de lire l'en-tête du CSV");
            echo "Erreur : Impossible de lire l'en-tête du CSV\n";
            fclose($handle);
            return;
        }

        echo "En-têtes détectées : " . implode(', ', $headers) . "\n";
        echo "Importation des opportunités...\n";

        $imported = 0;
        $errors = 0;

        // Pour PostgreSQL, pas besoin de désactiver les contraintes pour l'insertion simple

        while (($data = fgetcsv($handle, 0, ',')) !== FALSE) {
            try {
                // Créer un tableau associatif avec les données
                $rowData = array_combine($headers, $data);
                
                // Nettoyer et valider les données
                $opportuniteData = [
                    'institution' => $this->cleanString($rowData['institution'] ?? ''),
                    'institution_type' => $this->cleanString($rowData['institution_type'] ?? ''),
                    'statut' => $this->cleanString($rowData['statut'] ?? ''),
                    'titre' => $this->cleanString($rowData['titre'] ?? ''),
                    'description' => $this->cleanString($rowData['description'] ?? ''),
                    'type' => $this->cleanString($rowData['type'] ?? ''),
                    'pays' => $this->cleanString($rowData['pays'] ?? ''),
                    'regions_ciblees' => $this->cleanString($rowData['regions_ciblees'] ?? ''),
                    'date_limite_candidature' => $this->cleanString($rowData['date_limite_candidature'] ?? ''),
                    'date_debut' => $this->cleanString($rowData['date_debut'] ?? ''),
                    'duree' => $this->cleanString($rowData['duree'] ?? ''),
                    'remuneration' => $this->cleanString($rowData['remuneration'] ?? ''),
                    'nombre_places' => $this->cleanString($rowData['nombre_places'] ?? ''),
                    'secteurs' => $this->cleanString($rowData['secteurs'] ?? ''),
                    'criteres_eligibilite' => $this->cleanString($rowData['criteres_eligibilite'] ?? ''),
                    'contact_email_enrichi' => $this->cleanString($rowData['contact_email_enrichi'] ?? ''),
                    'lien_externe' => $this->cleanString($rowData['lien_externe'] ?? ''),
                    'origine_initiative' => $this->cleanString($rowData['origine_initiative'] ?? ''),
                ];

                // Valider que les champs obligatoires ne sont pas vides
                if (empty($opportuniteData['titre']) || empty($opportuniteData['institution'])) {
                    echo "Ligne ignorée : titre ou institution manquant\n";
                    $errors++;
                    continue;
                }

                Opportunite::create($opportuniteData);
                $imported++;

                if ($imported % 10 == 0) {
                    echo "Importé : {$imported} opportunités\n";
                }

            } catch (\Exception $e) {
                $errors++;
                Log::error("Erreur lors de l'import d'une opportunité", [
                    'error' => $e->getMessage(),
                    'data' => $data ?? []
                ]);
                echo "Erreur lors de l'import : " . $e->getMessage() . "\n";
            }
        }

        // Import terminé

        fclose($handle);

        echo "\n=== Import terminé ===\n";
        echo "Opportunités importées avec succès : {$imported}\n";
        echo "Erreurs rencontrées : {$errors}\n";
        echo "Total dans la base : " . Opportunite::count() . "\n";
    }

    /**
     * Nettoyer une chaîne de caractères
     */
    private function cleanString(?string $value): ?string
    {
        if (empty($value) || $value === 'null' || $value === 'NULL') {
            return null;
        }

        return trim($value);
    }
}
