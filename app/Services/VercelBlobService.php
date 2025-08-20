<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;

class VercelBlobService
{
    private string $token;
    private string $baseUrl;

    public function __construct()
    {
        $this->token = config('filesystems.disks.vercel-blob.token') ?? env('BLOB_READ_WRITE_TOKEN');
        $this->baseUrl = 'https://blob.vercel-storage.com';
    }

    /**
     * Upload a file to Vercel Blob storage
     */
    public function upload(UploadedFile|string $file, string $filename = null): array
    {
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
                $mimeType = 'application/octet-stream';
            }
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => $mimeType,
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