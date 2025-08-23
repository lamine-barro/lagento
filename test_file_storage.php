<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// Force production mode to test blob storage
putenv('APP_ENV=production');
$_ENV['APP_ENV'] = 'production';
config(['app.env' => 'production']);

try {
    echo "Testing FileStorageService with Vercel Blob...\n";
    echo "=============================================\n\n";
    
    $fileService = app(\App\Services\FileStorageService::class);
    
    // Check storage info
    $info = $fileService->getStorageInfo();
    echo "Storage Configuration:\n";
    echo "- Using Blob: " . ($info['using_blob'] ? 'Yes' : 'No') . "\n";
    echo "- Environment: " . $info['environment'] . "\n";
    echo "- Token configured: " . ($info['blob_token_set'] ? 'Yes' : 'No') . "\n\n";
    
    // Test with string content
    echo "📤 Testing upload with string content...\n";
    $testContent = "Test content from FileStorageService at " . date('Y-m-d H:i:s');
    $result = $fileService->storeGenerated($testContent, 'test_' . time() . '.txt', 'tests');
    
    if ($result && isset($result['url'])) {
        echo "✅ Upload successful!\n";
        echo "- URL: " . $result['url'] . "\n";
        echo "- Path: " . $result['path'] . "\n";
        echo "- Storage: " . $result['storage'] . "\n";
        echo "- Size: " . ($result['size'] ?? 'unknown') . " bytes\n\n";
        
        // Verify content
        echo "📥 Verifying content...\n";
        $fetchedContent = $fileService->getContents($result['url']);
        if ($fetchedContent === $testContent) {
            echo "✅ Content matches!\n\n";
        } else {
            echo "⚠️ Content mismatch\n\n";
        }
        
        // Test existence check
        echo "🔍 Testing existence check...\n";
        if ($fileService->exists($result['url'])) {
            echo "✅ File exists\n\n";
        } else {
            echo "⚠️ File not found\n\n";
        }
        
        // Test deletion
        echo "🗑️ Testing deletion...\n";
        if ($fileService->delete($result['url'])) {
            echo "✅ File deleted\n";
        } else {
            echo "⚠️ Could not delete file\n";
        }
    } else {
        echo "❌ Upload failed\n";
        print_r($result);
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}