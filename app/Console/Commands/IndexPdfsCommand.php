<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MemoryManagerService;
use App\Services\PdfExtractionService;
use App\Services\VoyageVectorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IndexPdfsCommand extends Command
{
    protected $signature = 'vector:index-pdfs {--batch=5 : Number of PDFs to process per batch} {--delay=3 : Delay between batches in seconds}';
    protected $description = 'Index all PDF files from textes_officiels with robust error handling';

    private PdfExtractionService $pdfExtractor;
    private VoyageVectorService $vectorService;

    public function __construct(PdfExtractionService $pdfExtractor, VoyageVectorService $vectorService)
    {
        parent::__construct();
        $this->pdfExtractor = $pdfExtractor;
        $this->vectorService = $vectorService;
    }

    public function handle()
    {
        $this->info('🚀 Starting PDF indexation...');
        
        $batchSize = (int) $this->option('batch');
        $delay = (int) $this->option('delay');
        
        // Load CSV data
        $csvPath = base_path('data/textes_officiels.csv');
        $pdfDir = base_path('data/textes_officiels_downloads');
        
        if (!file_exists($csvPath)) {
            $this->error('CSV file not found: ' . $csvPath);
            return 1;
        }

        $csv = array_map('str_getcsv', file($csvPath));
        $headers = array_shift($csv);
        
        $this->info("Found " . count($csv) . " records in CSV");
        
        // Clear existing textes officiels
        $this->warn('Clearing existing textes officiels vectors...');
        DB::table('vector_memories')
            ->where('memory_type', 'texte_officiel')
            ->delete();

        $processed = 0;
        $successful = 0;
        $failed = 0;
        
        // Process in batches
        $batches = array_chunk($csv, $batchSize);
        
        foreach ($batches as $batchIndex => $batch) {
            $this->info("Processing batch " . ($batchIndex + 1) . "/" . count($batches));
            
            foreach ($batch as $row) {
                if (count($row) !== count($headers)) {
                    continue;
                }
                
                $data = array_combine($headers, $row);
                $processed++;
                
                try {
                    $result = $this->processSinglePdf($data, $pdfDir);
                    
                    if ($result['success']) {
                        $successful++;
                        $this->line("✅ " . $data['titre'] . " (" . $result['chunks'] . " chunks)");
                    } else {
                        $failed++;
                        $this->line("❌ " . $data['titre'] . " - " . $result['error']);
                    }
                    
                } catch (\Exception $e) {
                    $failed++;
                    $this->error("❌ Error processing " . $data['titre'] . ": " . $e->getMessage());
                    Log::error('PDF indexation error', [
                        'file' => $data['titre'],
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Progress update
            $this->info("Batch completed. Progress: $successful successful, $failed failed");
            
            // Delay between batches
            if ($batchIndex < count($batches) - 1) {
                $this->info("Waiting {$delay}s before next batch...");
                sleep($delay);
            }
        }
        
        // Final report
        $this->newLine();
        $this->info("🎯 PDF Indexation Complete!");
        $this->table(['Metric', 'Count'], [
            ['Total processed', $processed],
            ['Successful', $successful],
            ['Failed', $failed],
            ['Success rate', round(($successful / max($processed, 1)) * 100, 1) . '%']
        ]);
        
        // Verify final state
        $totalChunks = DB::table('vector_memories')
            ->where('memory_type', 'texte_officiel')
            ->count();
            
        $pdfExtractedChunks = DB::table('vector_memories')
            ->where('memory_type', 'texte_officiel')
            ->whereJsonContains('metadata->pdf_extracted', true)
            ->count();
            
        $this->info("📊 Final state: $totalChunks total chunks, $pdfExtractedChunks with PDF content");
        
        return 0;
    }
    
    private function processSinglePdf(array $data, string $pdfDir): array
    {
        $classification = $data['classification_juridique'] ?? 'Non défini';
        $titre = $data['titre'] ?? '';
        $context = "Texte officiel $classification - $titre";
        
        // CSV metadata content
        $csvContent = implode("\n", array_filter([
            "Titre: " . $titre,
            "Classification: " . $classification,
            "Statut: " . ($data['statut'] ?? ''),
            "Date publication: " . ($data['date_publication'] ?? ''),
            "Taille fichier: " . ($data['taille_fichier'] ?? ''),
            "Type MIME: " . ($data['type_mime'] ?? '')
        ]));

        // Extract PDF content if available
        $pdfContent = '';
        $hasPdf = false;
        $pdfError = null;
        
        if (isset($data['fichier_telecharge']) && !empty($data['fichier_telecharge'])) {
            $pdfPath = $pdfDir . '/' . $data['fichier_telecharge'];
            
            if (file_exists($pdfPath)) {
                $extractedData = $this->pdfExtractor->extractWithMetadata($pdfPath);
                
                if (!empty($extractedData['content']) && strlen($extractedData['content']) > 100) {
                    $pdfContent = "\n\n=== CONTENU PDF ===\n" . $extractedData['content'];
                    $hasPdf = true;
                } else {
                    $pdfContent = "\nFichier PDF présent mais contenu non extractible: " . $data['fichier_telecharge'];
                    $pdfError = $extractedData['error'] ?? 'Empty content';
                }
            } else {
                $pdfContent = "\nFichier PDF référencé mais non trouvé: " . $data['fichier_telecharge'];
                $pdfError = 'File not found';
            }
        }

        $fullContent = $csvContent . $pdfContent;
        
        // Chunking with appropriate size
        $chunkSize = $hasPdf ? 800 : 600;
        $chunks = $this->vectorService->intelligentChunk($fullContent, $context, $chunkSize);
        
        if (empty($chunks)) {
            return ['success' => false, 'error' => 'No chunks generated', 'chunks' => 0];
        }
        
        // Process embeddings one by one to avoid token limits
        $successfulChunks = 0;
        
        foreach ($chunks as $index => $chunk) {
            try {
                $embeddings = $this->vectorService->embedWithContext([$chunk], $context);
                
                if (!empty($embeddings) && isset($embeddings[0])) {
                    DB::table('vector_memories')->insert([
                        'id' => Str::uuid(),
                        'memory_type' => 'texte_officiel',
                        'source_id' => 'csv_' . $data['id'] . '_' . $index,
                        'chunk_content' => $chunk,
                        'embedding' => json_encode($embeddings[0]['embedding']),
                        'metadata' => json_encode([
                            'classification' => $classification,
                            'date_publication' => $data['date_publication'] ?? null,
                            'source' => 'csv',
                            'has_pdf' => $hasPdf,
                            'pdf_extracted' => $hasPdf,
                            'file_name' => $data['fichier_telecharge'] ?? null,
                            'statut' => $data['statut'] ?? null,
                            'pdf_error' => $pdfError
                        ]),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    $successfulChunks++;
                }
                
                // Small delay between chunks to respect API limits
                usleep(500000); // 0.5 second
                
            } catch (\Exception $e) {
                Log::warning('Failed to process chunk', [
                    'file' => $titre,
                    'chunk_index' => $index,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return [
            'success' => $successfulChunks > 0,
            'error' => $successfulChunks === 0 ? 'No chunks successfully processed' : null,
            'chunks' => $successfulChunks,
            'pdf_extracted' => $hasPdf
        ];
    }
}