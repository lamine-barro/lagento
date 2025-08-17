<?php

namespace Tests\Feature;

use App\Models\MorceauDocument;
use App\Services\EmbeddingService;
use App\Services\SemanticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmbeddingSystemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that document chunks can be created
     */
    public function test_document_chunks_can_be_created(): void
    {
        $chunk = MorceauDocument::create([
            'type_source' => 'test',
            'source_id' => (string) \Illuminate\Support\Str::uuid(),
            'contenu' => 'Test content for embedding',
            'embedding' => array_fill(0, 1024, 0.1) // Fake embedding
        ]);

        $this->assertInstanceOf(MorceauDocument::class, $chunk);
        $this->assertEquals('test', $chunk->type_source);
        $this->assertEquals('Test content for embedding', $chunk->contenu);
        $this->assertIsArray($chunk->embedding);
        $this->assertCount(1024, $chunk->embedding);
    }

    /**
     * Test that embedding service can chunk text
     */
    public function test_embedding_service_can_chunk_text(): void
    {
        $service = app(EmbeddingService::class);
        
        // Test with shorter text that should result in single chunk
        $shortText = 'This is a short text.';
        $shortChunks = $service->contextualChunk($shortText, 500, 50);
        $this->assertCount(1, $shortChunks);
        $this->assertEquals($shortText, $shortChunks[0]);
        
        // Test with longer text
        $longText = str_repeat('This is a paragraph.\n\n', 50);
        $longChunks = $service->contextualChunk($longText, 200, 50);
        
        $this->assertIsArray($longChunks);
        $this->assertGreaterThanOrEqual(1, count($longChunks));
        
        // Verify each chunk is reasonable
        foreach ($longChunks as $chunk) {
            $this->assertNotEmpty(trim($chunk));
        }
    }

    /**
     * Test document chunk scopes
     */
    public function test_document_chunk_scopes(): void
    {
        // Create test chunks
        MorceauDocument::create([
            'type_source' => 'official_text',
            'source_id' => (string) \Illuminate\Support\Str::uuid(),
            'contenu' => 'Official text content',
            'embedding' => array_fill(0, 1024, 0.1)
        ]);
        
        MorceauDocument::create([
            'type_source' => 'opportunity',
            'source_id' => (string) \Illuminate\Support\Str::uuid(),
            'contenu' => 'Opportunity content',
            'embedding' => array_fill(0, 1024, 0.2)
        ]);

        // Test type filtering
        $officialChunks = MorceauDocument::ofType('official_text')->get();
        $this->assertCount(1, $officialChunks);
        $this->assertEquals('Official text content', $officialChunks->first()->contenu);

        // Test source filtering
        $sourceId = MorceauDocument::where('type_source', 'opportunity')->first()->source_id;
        $sourceChunks = MorceauDocument::fromSource('opportunity', $sourceId)->get();
        $this->assertCount(1, $sourceChunks);
        $this->assertEquals('Opportunity content', $sourceChunks->first()->contenu);
    }

    /**
     * Test semantic search service instantiation
     */
    public function test_semantic_search_service_can_be_instantiated(): void
    {
        $service = app(SemanticSearchService::class);
        $this->assertInstanceOf(SemanticSearchService::class, $service);
    }

    /**
     * Test that we can query document chunks (basic functionality without API calls)
     */
    public function test_document_chunks_can_be_queried(): void
    {
        // Create test chunks
        MorceauDocument::create([
            'type_source' => 'test',
            'source_id' => (string) \Illuminate\Support\Str::uuid(),
            'contenu' => 'Entrepreneurship in Côte d\'Ivoire',
            'embedding' => array_fill(0, 1024, 0.5)
        ]);
        
        MorceauDocument::create([
            'type_source' => 'test',
            'source_id' => (string) \Illuminate\Support\Str::uuid(),
            'contenu' => 'Technology startups in Africa',
            'embedding' => array_fill(0, 1024, 0.3)
        ]);

        $chunks = MorceauDocument::all();
        $this->assertCount(2, $chunks);
        
        // Test that we can filter by type
        $testChunks = MorceauDocument::ofType('test')->get();
        $this->assertCount(2, $testChunks);
    }
}
