<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PdfExtractionService
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Extract text content from PDF file
     */
    public function extractTextFromPdf(string $filePath): string
    {
        try {
            if (!file_exists($filePath)) {
                Log::warning('PDF file not found', ['path' => $filePath]);
                return '';
            }

            $pdf = $this->parser->parseFile($filePath);
            $text = $pdf->getText();
            
            // Clean and normalize extracted text
            $cleanedText = $this->cleanExtractedText($text);
            
            Log::info('PDF text extracted successfully', [
                'file' => basename($filePath),
                'original_length' => strlen($text),
                'cleaned_length' => strlen($cleanedText)
            ]);

            return $cleanedText;

        } catch (\Exception $e) {
            Log::error('PDF extraction failed', [
                'file' => basename($filePath),
                'error' => $e->getMessage()
            ]);
            return '';
        }
    }

    /**
     * Extract text with metadata from PDF
     */
    public function extractWithMetadata(string $filePath): array
    {
        try {
            if (!file_exists($filePath)) {
                return [
                    'content' => '',
                    'metadata' => [],
                    'error' => 'File not found'
                ];
            }

            $pdf = $this->parser->parseFile($filePath);
            $text = $pdf->getText();
            $details = $pdf->getDetails();
            
            $cleanedText = $this->cleanExtractedText($text);

            return [
                'content' => $cleanedText,
                'metadata' => [
                    'title' => $details['Title'] ?? '',
                    'author' => $details['Author'] ?? '',
                    'subject' => $details['Subject'] ?? '',
                    'creator' => $details['Creator'] ?? '',
                    'producer' => $details['Producer'] ?? '',
                    'creation_date' => $details['CreationDate'] ?? '',
                    'modification_date' => $details['ModDate'] ?? '',
                    'pages_count' => count($pdf->getPages()),
                    'file_size' => filesize($filePath),
                    'extracted_length' => strlen($cleanedText)
                ],
                'error' => null
            ];

        } catch (\Exception $e) {
            Log::error('PDF extraction with metadata failed', [
                'file' => basename($filePath),
                'error' => $e->getMessage()
            ]);

            return [
                'content' => '',
                'metadata' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Extract text from all PDFs in a directory
     */
    public function extractFromDirectory(string $directory): array
    {
        $results = [];
        
        if (!is_dir($directory)) {
            Log::warning('PDF directory not found', ['path' => $directory]);
            return $results;
        }

        $pdfFiles = glob($directory . '/*.pdf');
        
        foreach ($pdfFiles as $pdfPath) {
            $fileName = basename($pdfPath);
            $extraction = $this->extractWithMetadata($pdfPath);
            
            $results[$fileName] = $extraction;
            
            // Add small delay to prevent memory issues with large PDFs
            if (count($results) % 5 === 0) {
                sleep(1);
            }
        }

        Log::info('Bulk PDF extraction completed', [
            'directory' => $directory,
            'files_processed' => count($results),
            'successful_extractions' => count(array_filter($results, fn($r) => !empty($r['content'])))
        ]);

        return $results;
    }

    /**
     * Clean and normalize extracted text
     */
    private function cleanExtractedText(string $text): string
    {
        // Remove excessive whitespace and normalize
        $text = preg_replace('/\s+/', ' ', trim($text));
        
        // Remove common PDF artifacts
        $text = str_replace(["\x00", "\x0B", "\x0C"], '', $text);
        
        // Fix common encoding issues
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        
        // Remove lines that are too short (likely artifacts)
        $lines = explode("\n", $text);
        $cleanLines = array_filter($lines, function($line) {
            return strlen(trim($line)) > 10; // Keep lines with more than 10 chars
        });
        
        $cleanedText = implode("\n", $cleanLines);
        
        // Ensure minimum content length
        if (strlen($cleanedText) < 100) {
            return $text; // Return original if cleaning removed too much
        }
        
        return $cleanedText;
    }

    /**
     * Check if PDF is readable/extractable
     */
    public function isPdfReadable(string $filePath): bool
    {
        try {
            if (!file_exists($filePath)) {
                return false;
            }

            $pdf = $this->parser->parseFile($filePath);
            $text = $pdf->getText();
            
            return strlen(trim($text)) > 50; // Has meaningful content
            
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get PDF basic info without full extraction
     */
    public function getPdfInfo(string $filePath): array
    {
        try {
            if (!file_exists($filePath)) {
                return [];
            }

            $pdf = $this->parser->parseFile($filePath);
            $details = $pdf->getDetails();
            
            return [
                'file_name' => basename($filePath),
                'file_size' => filesize($filePath),
                'pages_count' => count($pdf->getPages()),
                'title' => $details['Title'] ?? '',
                'author' => $details['Author'] ?? '',
                'creation_date' => $details['CreationDate'] ?? '',
                'readable' => $this->isPdfReadable($filePath)
            ];

        } catch (\Exception $e) {
            return [
                'file_name' => basename($filePath),
                'error' => $e->getMessage(),
                'readable' => false
            ];
        }
    }
}