<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing Vercel Blob Upload...\n";
    echo "================================\n\n";
    
    // Check token
    $token = env('VERCEL_BLOB_READ_WRITE_TOKEN');
    if (!$token || $token === 'vercel_blob_rw_placeholder') {
        echo "❌ Token not configured properly\n";
        echo "Token value: " . ($token ?: 'empty') . "\n";
        exit(1);
    }
    echo "✅ Token is configured\n";
    
    // Create service
    $blobService = new \App\Services\VercelBlobService();
    
    // Create test content
    $testContent = "Test upload from Laravel at " . date('Y-m-d H:i:s');
    $testFilename = 'test/test_' . time() . '.txt';
    
    echo "\n📤 Uploading test file: $testFilename\n";
    echo "Content: $testContent\n\n";
    
    // Try upload
    $result = $blobService->upload($testContent, $testFilename);
    
    if ($result && isset($result['url'])) {
        echo "✅ Upload successful!\n";
        echo "URL: " . $result['url'] . "\n";
        echo "Size: " . ($result['size'] ?? 'unknown') . " bytes\n";
        
        // Try to fetch the content
        echo "\n📥 Verifying upload by fetching content...\n";
        $fetchedContent = file_get_contents($result['url']);
        if ($fetchedContent === $testContent) {
            echo "✅ Content verified successfully!\n";
        } else {
            echo "⚠️ Content mismatch\n";
            echo "Expected: $testContent\n";
            echo "Got: $fetchedContent\n";
        }
        
        // Try to delete
        echo "\n🗑️ Testing deletion...\n";
        $deleted = $blobService->delete($result['url']);
        if ($deleted) {
            echo "✅ File deleted successfully\n";
        } else {
            echo "⚠️ Could not delete file\n";
        }
    } else {
        echo "❌ Upload failed - no URL returned\n";
        print_r($result);
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}