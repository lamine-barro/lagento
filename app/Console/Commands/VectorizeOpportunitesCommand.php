<?php

namespace App\Console\Commands;

use App\Services\OpenAIVectorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VectorizeOpportunitesCommand extends Command
{
    protected $signature = 'vectorize:opportunites 
                          {--file=data/opportunites.csv : Path to opportunities file}
                          {--namespace=opportunites : Pinecone namespace}';
    
    protected $description = 'Vectorize opportunities CSV file as a single chunk using OpenAI embeddings and store in Pinecone';

    protected OpenAIVectorService $vectorService;

    public function __construct(OpenAIVectorService $vectorService)
    {
        parent::__construct();
        $this->vectorService = $vectorService;
    }

    public function handle()
    {
        $filePath = base_path($this->option('file'));
        $namespace = $this->option('namespace');
        
        if (!file_exists($filePath)) {
            $this->error("Opportunities file not found: {$filePath}");
            return 1;
        }

        $this->info("🚀 Starting vectorization of opportunities...");
        $this->info("📁 File: {$filePath}");
        $this->info("📦 Namespace: {$namespace}");
        $this->info("🎯 Target: Single chunk (no overlap)");

        try {
            // Read and process the CSV file
            $csvContent = file_get_contents($filePath);
            $lines = array_filter(explode("\n", $csvContent));
            $lineCount = count($lines) - 1; // Minus header
            
            $this->info("📄 CSV lines: {$lineCount} opportunities");

            // Delete existing vectors in namespace
            $this->warn("🗑️  Deleting existing vectors in namespace '{$namespace}'...");
            $deleted = $this->vectorService->deleteVectors(null, $namespace);
            
            if ($deleted) {
                $this->info("✅ Existing vectors deleted successfully");
            } else {
                $this->warn("⚠️  Failed to delete existing vectors or none existed");
            }

            // Format CSV content for better readability
            $formattedContent = $this->formatCsvContent($csvContent);

            // Process with smaller chunks to stay under token limits
            $this->info("🔄 Processing with smaller chunks to avoid OpenAI token limits...");
            
            $success = $this->vectorService->processAndStore(
                $formattedContent,
                'opportunites_master',
                [
                    'type' => 'opportunites',
                    'source' => 'opportunites.csv',
                    'version' => date('Y-m-d_H-i-s'),
                    'line_count' => $lineCount,
                    'content_length' => strlen($formattedContent),
                    'description' => 'Complete opportunities database: ' . $lineCount . ' opportunities across CI'
                ],
                $namespace,
                4000, // Smaller chunks to fit under 8192 token limit
                0.0   // No overlap as requested
            );

            if ($success) {
                $this->info("✅ Vectorization completed successfully!");
                $this->info("🎯 {$lineCount} opportunities stored as multiple vectors in Pinecone");
                
                // Test search to verify
                $this->info("🔍 Testing search functionality...");
                $results = $this->vectorService->searchSimilar(
                    query: "programmes d'incubation pour startups",
                    topK: 3,
                    namespace: $namespace
                );
                
                if (!empty($results)) {
                    $this->info("✅ Search test successful - found " . count($results) . " relevant results");
                    foreach ($results as $result) {
                        $score = round($result['score'], 3);
                        $institution = $this->extractInstitutionFromContent($result['metadata']['content'] ?? '');
                        $this->line("  📌 Score: {$score} - {$institution}");
                    }
                } else {
                    $this->warn("⚠️  Search test returned no results");
                }
                
                Log::info('Opportunities vectorization completed', [
                    'line_count' => $lineCount,
                    'namespace' => $namespace,
                    'vector_id' => 'opportunites_master'
                ]);
                
            } else {
                $this->error("❌ Failed to store vector in Pinecone!");
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("❌ Error during vectorization: " . $e->getMessage());
            Log::error('Opportunities vectorization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        $this->info("🎉 Opportunities vectorization process completed!");
        return 0;
    }

    /**
     * Format CSV content for better semantic understanding
     */
    private function formatCsvContent(string $csvContent): string
    {
        $lines = explode("\n", $csvContent);
        $header = str_getcsv($lines[0]);
        
        $formattedLines = ["=== BASE DE DONNÉES DES OPPORTUNITÉS ENTREPRENEURIALES EN CÔTE D'IVOIRE ===\n"];
        
        // Add summary
        $totalLines = count($lines) - 1;
        $formattedLines[] = "RÉSUMÉ: {$totalLines} opportunités disponibles pour entrepreneurs ivoiriens";
        $formattedLines[] = "TYPES: Incubation, Accélération, Financement, Formation";
        $formattedLines[] = "SECTEURS: Numérique, Agriculture, Santé, Finance, Environnement, Tous secteurs\n";
        
        // Process each opportunity
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;
            
            // Parse CSV line with proper delimiter handling
            $data = str_getcsv($line, ',', '"', '\\');
            
            // Skip lines that don't match header count
            if (count($data) !== count($header)) {
                $this->warn("Skipping line " . ($i) . ": Column count mismatch. Expected " . count($header) . ", got " . count($data));
                continue;
            }
            
            // Ensure arrays have same length before combining
            if (empty($header) || empty($data)) {
                $this->warn("Skipping line " . ($i) . ": Empty header or data");
                continue;
            }
            
            $opportunity = array_combine($header, $data);
            
            $formattedLines[] = "--- OPPORTUNITÉ " . ($i) . " ---";
            $formattedLines[] = "INSTITUTION: " . ($opportunity['institution_id'] ?? '');
            $formattedLines[] = "TITRE: " . ($opportunity['titre'] ?? '');
            $formattedLines[] = "TYPE: " . ($opportunity['type'] ?? '');
            $formattedLines[] = "STATUT: " . ($opportunity['statut'] ?? '');
            $formattedLines[] = "DESCRIPTION: " . ($opportunity['description'] ?? '');
            $formattedLines[] = "PAYS: " . ($opportunity['pays'] ?? '');
            $formattedLines[] = "VILLE: " . ($opportunity['ville'] ?? '');
            $formattedLines[] = "DATE LIMITE: " . ($opportunity['date_limite_candidature'] ?? '');
            $formattedLines[] = "DURÉE: " . ($opportunity['duree'] ?? '');
            $formattedLines[] = "RÉMUNÉRATION: " . ($opportunity['remuneration'] ?? '');
            $formattedLines[] = "PLACES: " . ($opportunity['nombre_places'] ?? '');
            $formattedLines[] = "CRITÈRES: " . ($opportunity['criteres_eligibilite'] ?? '');
            $formattedLines[] = "CONTACT: " . ($opportunity['contact_email'] ?? '');
            $formattedLines[] = "SECTEURS: " . ($opportunity['secteurs'] ?? '');
            $formattedLines[] = "";
        }
        
        return implode("\n", $formattedLines);
    }

    /**
     * Extract institution name from content for display
     */
    private function extractInstitutionFromContent(string $content): string
    {
        if (preg_match('/INSTITUTION: ([^\n]+)/', $content, $matches)) {
            return trim($matches[1]);
        }
        return 'Opportunité trouvée';
    }
}