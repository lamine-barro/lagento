<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Opportunite;
use App\Constants\BusinessConstants;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ImportOpportunitesCommand extends Command
{
    protected $signature = 'import:opportunites 
                          {--file=data/opportunites.csv : Path to CSV file}
                          {--clear : Clear existing opportunities}';
    
    protected $description = 'Import opportunities from CSV file using app constants';

    public function handle()
    {
        $csvFile = base_path($this->option('file'));
        
        if (!file_exists($csvFile)) {
            $this->error("CSV file not found: $csvFile");
            return 1;
        }

        // Clear existing opportunities if requested
        if ($this->option('clear')) {
            $this->info('🗑️  Clearing existing opportunities...');
            Opportunite::truncate();
        }

        $this->info('📖 Reading CSV file...');
        
        $handle = fopen($csvFile, 'r');
        $headers = fgetcsv($handle); // Skip headers
        
        $imported = 0;
        $errors = 0;

        while (($row = fgetcsv($handle)) !== false) {
            try {
                // Skip if row doesn't match header count
                if (count($row) !== count($headers)) {
                    $this->warn("⚠️  Skipping row with mismatched columns");
                    continue;
                }
                
                $data = array_combine($headers, $row);
                
                $opportunity = $this->createOpportunityFromRow($data);
                
                if ($opportunity) {
                    $imported++;
                    $this->line("✅ {$opportunity->titre}");
                } else {
                    $errors++;
                }
                
            } catch (\Exception $e) {
                $errors++;
                $this->error("❌ Error importing row: " . $e->getMessage());
            }
        }
        
        fclose($handle);
        
        $this->newLine();
        $this->info("📊 Import completed!");
        $this->info("✅ Imported: $imported opportunities");
        $this->info("❌ Errors: $errors");
        
        return 0;
    }

    private function createOpportunityFromRow(array $data): ?Opportunite
    {
        // Generate ID from institution_id and titre
        $id = Str::slug($data['institution_id'] . '-' . $data['titre']);
        
        // Parse date fields
        $dateLimite = $this->parseDate($data['date_limite_candidature']);
        $dateDebut = $this->parseDate($data['date_debut']);
        
        // Parse regions
        $regions = $this->parseRegions($data['regions_ciblees']);
        
        // Parse secteurs from CSV and map to constants
        $secteurs = [];
        if (!empty($data['secteurs'])) {
            $csvSecteurs = array_map('trim', explode(';', $data['secteurs']));
            foreach ($csvSecteurs as $secteur) {
                // Map CSV secteurs to app constants
                $mapped = $this->mapSecteur($secteur);
                if ($mapped) {
                    $secteurs[] = $mapped;
                }
            }
        }

        // Validate and map type using app constants
        $type = $this->mapType($data['type']);
        
        // Add institution_id to description for now
        $description = $data['description'];
        if (!empty($data['institution_id'])) {
            $description .= "\n\nInstitution: " . $data['institution_id'];
        }

        return Opportunite::create([
            'id' => $id,
            'institution_id' => null, // Set to null since we don't have UUID institutions yet
            'statut' => $this->mapStatut($data['statut']),
            'titre' => $data['titre'],
            'type' => $type,
            'description' => $description,
            'pays' => $data['pays'] ?? 'Côte d\'Ivoire',
            'regions_cibles' => $regions,
            'ville' => $data['ville'] ?? null,
            'date_limite' => $dateLimite,
            'date_debut' => $dateDebut,
            'duree' => $data['duree'] ?? null,
            'remuneration' => $data['remuneration'] ?? null,
            'places' => $this->parseNumber($data['nombre_places']),
            'criteres_eligibilite' => $this->parseCriteres($data['criteres_eligibilite']),
            'email_contact' => $data['contact_email'] ?? null,
            'lien_externe' => $data['lien_externe'] ?? null,
            'secteurs' => $secteurs,
        ]);
    }

    private function parseDate(?string $dateStr): ?Carbon
    {
        if (!$dateStr || $dateStr === 'Continu' || $dateStr === 'En cours') {
            return null;
        }
        
        // Handle specific date formats
        if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $dateStr, $matches)) {
            return Carbon::createFromFormat('d/m/Y', $dateStr);
        }
        
        if (preg_match('/(\d{4})/', $dateStr, $matches)) {
            return Carbon::createFromFormat('Y', $matches[1]);
        }
        
        return null;
    }

    private function parseRegions(string $regionsStr): array
    {
        if (empty($regionsStr) || $regionsStr === 'National') {
            return ['National'];
        }
        
        $regions = array_map('trim', explode(',', $regionsStr));
        $validRegions = [];
        
        $appRegions = array_keys(BusinessConstants::REGIONS);
        
        foreach ($regions as $region) {
            if (in_array($region, $appRegions)) {
                $validRegions[] = $region;
            } elseif ($region === 'National') {
                $validRegions[] = 'National';
            }
        }
        
        return empty($validRegions) ? ['National'] : $validRegions;
    }

    private function mapType(string $csvType): string
    {
        $typeMap = [
            'INCUBATION' => 'INCUBATION',
            'ACCELERATION' => 'ACCELERATION', 
            'FINANCEMENT' => 'FINANCEMENT',
            'FORMATION' => 'FORMATION',
            'ASSISTANCE_TECHNIQUE' => 'ASSISTANCE_TECHNIQUE',
            'APPEL_OFFRES' => 'APPEL_OFFRES',
        ];

        return $typeMap[$csvType] ?? 'FORMATION';
    }

    private function mapStatut(string $csvStatut): string
    {
        $statutMap = [
            'Ouvert' => 'ouvert',
            'En cours' => 'en_cours',
            'À venir' => 'a_venir',
            'Fermé' => 'ferme',
        ];

        return $statutMap[$csvStatut] ?? 'ouvert';
    }

    private function parseNumber(?string $numberStr): ?int
    {
        if (!$numberStr || $numberStr === 'Illimité' || $numberStr === 'Variable') {
            return null;
        }
        
        // Extract number from string like "20 par cohorte"
        if (preg_match('/(\d+)/', $numberStr, $matches)) {
            return (int) $matches[1];
        }
        
        return null;
    }

    private function parseCriteres(?string $criteresStr): array
    {
        if (!$criteresStr) {
            return [];
        }
        
        // Split by comma and clean up
        $criteres = array_map('trim', explode(',', $criteresStr));
        
        return array_filter($criteres);
    }

    private function mapSecteur(string $csvSecteur): ?string
    {
        $secteurMap = [
            'AGRICULTURE' => 'AGRICULTURE',
            'ENVIRONNEMENT' => 'ENVIRONNEMENT',
            'NUMERIQUE' => 'NUMERIQUE',
            'FINANCE' => 'FINANCE',
            'SANTE' => 'SANTE',
            'EDUCATION' => 'EDUCATION',
            'COMMERCE' => 'COMMERCE',
            'INDUSTRIE' => 'INDUSTRIE',
            'ENERGIE' => 'ENERGIE',
            'TRANSPORT' => 'TRANSPORT',
            'CONSTRUCTION' => 'CONSTRUCTION',
            'TOURISME' => 'TOURISME',
            'TOUS_SECTEURS' => 'TOUS_SECTEURS',
        ];

        return $secteurMap[$csvSecteur] ?? null;
    }
}