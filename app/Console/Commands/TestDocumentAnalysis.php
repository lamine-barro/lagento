<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DocumentAnalysisService;

class TestDocumentAnalysis extends Command
{
    protected $signature = 'test:document-analysis {content : Content to analyze}';
    protected $description = 'Test document analysis with sample content';

    private DocumentAnalysisService $analysisService;

    public function __construct(DocumentAnalysisService $analysisService)
    {
        parent::__construct();
        $this->analysisService = $analysisService;
    }

    public function handle()
    {
        $content = $this->argument('content');
        
        $this->info('Testing document analysis with GPT-4.1-mini...');
        $this->line('Content: ' . substr($content, 0, 100) . '...');
        $this->newLine();

        try {
            // Test du prompt et de l'analyse
            $result = $this->analysisService->analyzeWithGPT($content, 'test-document.txt');
            
            $this->info('✅ Analysis completed successfully!');
            $this->newLine();
            
            $this->line('📝 Summary:');
            $this->line($result['summary']);
            $this->newLine();
            
            $this->line('🏷️  Detected Tags:');
            foreach ($result['tags'] as $tag) {
                $this->line('  - ' . $tag);
            }
            $this->newLine();
            
            $this->line('📊 Metadata:');
            if (isset($result['metadata']['document_type'])) {
                $this->line('  Type: ' . $result['metadata']['document_type']);
            }
            if (isset($result['metadata']['confidence'])) {
                $this->line('  Confidence: ' . round($result['metadata']['confidence'] * 100) . '%');
            }
            if (isset($result['metadata']['key_info'])) {
                $this->line('  Key Info: ' . $result['metadata']['key_info']);
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Analysis failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}