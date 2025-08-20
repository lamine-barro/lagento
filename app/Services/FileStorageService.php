<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service centralisé pour la gestion des fichiers
 * Utilise Vercel Blob Storage pour tous les uploads en production
 */
class FileStorageService
{
    private VercelBlobService $blobService;
    private bool $useBlob;

    public function __construct(VercelBlobService $blobService)
    {
        $this->blobService = $blobService;
        $this->useBlob = config('app.env') === 'production' && config('filesystems.disks.vercel-blob.token');
    }

    /**
     * Store a file and return its public URL
     */
    public function store(UploadedFile|string $file, string $directory = '', string $filename = null): array
    {
        try {
            if ($this->useBlob) {
                return $this->storeOnBlob($file, $directory, $filename);
            } else {
                return $this->storeLocally($file, $directory, $filename);
            }
        } catch (\Exception $e) {
            Log::error('File storage failed', [
                'error' => $e->getMessage(),
                'directory' => $directory,
                'filename' => $filename
            ]);
            throw $e;
        }
    }

    /**
     * Store specifically for logos (compress and optimize)
     */
    public function storeLogo(UploadedFile|string $file, string $userId): array
    {
        $filename = 'logo_' . $userId . '_' . uniqid() . '.jpg';
        return $this->store($file, 'logos', $filename);
    }

    /**
     * Store chat attachments
     */
    public function storeChatAttachment(UploadedFile $file, string $userId): array
    {
        $extension = $file->getClientOriginalExtension();
        $filename = 'chat_' . $userId . '_' . uniqid() . '.' . $extension;
        return $this->store($file, 'chat-attachments', $filename);
    }

    /**
     * Store documents
     */
    public function storeDocument(UploadedFile $file, string $userId): array
    {
        $extension = $file->getClientOriginalExtension();
        $filename = 'doc_' . $userId . '_' . time() . '_' . Str::random(6) . '.' . $extension;
        return $this->store($file, 'documents/' . $userId, $filename);
    }

    /**
     * Store AI-generated files (images, documents, etc.)
     */
    public function storeGenerated(string $content, string $filename, string $directory = 'generated'): array
    {
        $fullFilename = date('Y/m/d') . '/' . $filename;
        return $this->store($content, $directory, $fullFilename);
    }

    /**
     * Store temporary files for processing
     */
    public function storeTemp(UploadedFile|string $file, string $filename = null): array
    {
        $filename = $filename ?: 'temp_' . uniqid();
        return $this->store($file, 'temp', $filename);
    }

    /**
     * Delete a file
     */
    public function delete(string $url): bool
    {
        try {
            if ($this->useBlob) {
                return $this->blobService->delete($url);
            } else {
                // For local files, extract the path and delete
                $path = str_replace(asset('storage/'), '', $url);
                return \Storage::disk('public')->delete($path);
            }
        } catch (\Exception $e) {
            Log::error('File deletion failed', [
                'error' => $e->getMessage(),
                'url' => $url
            ]);
            return false;
        }
    }

    /**
     * Get file contents (for reading/processing)
     */
    public function getContents(string $url): string
    {
        if ($this->useBlob) {
            // For Blob storage, fetch via HTTP
            return file_get_contents($url);
        } else {
            // For local files
            $path = str_replace(asset('storage/'), '', $url);
            return \Storage::disk('public')->get($path);
        }
    }

    /**
     * Check if file exists
     */
    public function exists(string $url): bool
    {
        if ($this->useBlob) {
            $headers = get_headers($url, 1);
            return strpos($headers[0], '200') !== false;
        } else {
            $path = str_replace(asset('storage/'), '', $url);
            return \Storage::disk('public')->exists($path);
        }
    }

    /**
     * Store on Vercel Blob
     */
    private function storeOnBlob(UploadedFile|string $file, string $directory, string $filename): array
    {
        $fullPath = $directory ? $directory . '/' . $filename : $filename;
        
        if ($file instanceof UploadedFile) {
            $result = $this->blobService->upload($file, $fullPath);
        } else {
            $result = $this->blobService->upload($file, $fullPath);
        }

        return [
            'url' => $result['url'],
            'path' => $fullPath,
            'size' => $result['size'] ?? null,
            'storage' => 'blob'
        ];
    }

    /**
     * Store locally (development/fallback)
     */
    private function storeLocally(UploadedFile|string $file, string $directory, string $filename): array
    {
        $fullPath = $directory ? $directory . '/' . $filename : $filename;
        
        if ($file instanceof UploadedFile) {
            $path = $file->storeAs($directory, $filename, 'public');
        } else {
            $path = $fullPath;
            \Storage::disk('public')->put($path, $file);
        }

        return [
            'url' => asset('storage/' . $path),
            'path' => $path,
            'storage' => 'local'
        ];
    }

    /**
     * Get storage info for debugging
     */
    public function getStorageInfo(): array
    {
        return [
            'using_blob' => $this->useBlob,
            'environment' => config('app.env'),
            'blob_token_set' => !empty(config('filesystems.disks.vercel-blob.token')),
            'blob_base_url' => config('filesystems.disks.vercel-blob.url')
        ];
    }
}