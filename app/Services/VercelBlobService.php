<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;

class VercelBlobService
{
    private ?string $token;
    private string $baseUrl;

    public function __construct()
    {
        $token = env('VERCEL_BLOB_READ_WRITE_TOKEN') ?? config('filesystems.disks.vercel-blob.token');
        
        // Don't assign placeholder values
        if ($token && $token !== 'vercel_blob_rw_placeholder') {
            $this->token = $token;
        } else {
            $this->token = null;
        }
        
        $this->baseUrl = 'https://blob.vercel-storage.com';
    }

    /**
     * Upload a file to Vercel Blob storage
     */
    public function upload(UploadedFile|string $file, string $filename = null): array
    {
        // If no token is configured, throw an exception
        if (!$this->token) {
            throw new \Exception('Vercel Blob token is not configured. Please set VERCEL_BLOB_READ_WRITE_TOKEN in your .env file.');
        }
        
        if ($file instanceof UploadedFile) {
            $filename = $filename ?? $file->getClientOriginalName();
            $content = $file->getContent();
            $mimeType = $file->getMimeType();
        } else {
            // If it's a string (file path or content)
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $mimeType = mime_content_type($file);
                $filename = $filename ?? basename($file);
            } else {
                $content = $file;
                // Detect mime type based on filename or content
                if (str_ends_with($filename, '.png')) {
                    $mimeType = 'image/png';
                } elseif (str_ends_with($filename, '.jpg') || str_ends_with($filename, '.jpeg')) {
                    $mimeType = 'image/jpeg';
                } elseif (str_ends_with($filename, '.txt')) {
                    $mimeType = 'text/plain';
                } elseif (str_ends_with($filename, '.md')) {
                    $mimeType = 'text/markdown';
                } elseif (str_ends_with($filename, '.csv')) {
                    $mimeType = 'text/csv';
                } elseif (str_ends_with($filename, '.docx')) {
                    $mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                } else {
                    $mimeType = 'application/octet-stream';
                }
            }
        }

        // Use PUT request with correct Vercel Blob API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => $mimeType,
            'x-vercel-filename' => basename($filename),
        ])->put($this->baseUrl . '/' . $filename, $content);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Failed to upload to Vercel Blob: ' . $response->body());
    }

    /**
     * Delete a file from Vercel Blob storage
     */
    public function delete(string $url): bool
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->delete($this->baseUrl . '/delete', [
            'urls' => [$url]
        ]);

        return $response->successful();
    }

    /**
     * Get the public URL for a blob
     */
    public function getUrl(string $filename): string
    {
        return 'https://68oqqdazslwpwzti.public.blob.vercel-storage.com/' . $filename;
    }

    /**
     * List all blobs (if supported)
     */
    public function list(): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get($this->baseUrl . '/list');

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }
}