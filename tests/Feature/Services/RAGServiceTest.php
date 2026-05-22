<?php

namespace Tests\Feature\Services;

use App\Models\KnowledgeBaseCategory;
use App\Models\KnowledgeBaseChunk;
use App\Models\KnowledgeBaseDocument;
use App\Services\AI\EmbeddingService;
use App\Services\AI\RAGService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RAGServiceTest extends TestCase
{
    use RefreshDatabase;

    private RAGService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $embedding = $this->createMock(EmbeddingService::class);
        $embedding->method('embedText')->willReturn([0.1, 0.2, 0.3]);
        app()->instance(EmbeddingService::class, $embedding);

        $this->service = app(RAGService::class);
    }

    public function test_ask_returns_no_result_when_no_chunks(): void
    {
        $result = $this->service->ask('test question');

        $this->assertStringContainsString('tidak ditemukan', $result['answer']);
        $this->assertEmpty($result['sources']);
        $this->assertEquals(0, $result['chunks_used']);
    }

    public function test_search_similar_returns_empty_when_no_chunks(): void
    {
        $result = $this->service->searchSimilar([0.1, 0.2, 0.3]);

        $this->assertEmpty($result);
    }

    public function test_search_similar_returns_scored_chunks(): void
    {
        $doc = KnowledgeBaseDocument::create([
            'judul' => 'Dokumen Akreditasi', 'sumber' => 'BAN-PT',
            'file_path' => 'test.pdf', 'file_size' => 100, 'status' => 'active',
        ]);

        KnowledgeBaseChunk::create([
            'document_id' => $doc->id, 'chunk_index' => 0,
            'content' => 'Standar akreditasi perguruan tinggi',
            'embedding' => [0.1, 0.2, 0.3],
        ]);

        $result = $this->service->searchSimilar([0.1, 0.2, 0.3]);

        $this->assertCount(1, $result);
        $this->assertEquals('Dokumen Akreditasi', $result[0]['document_judul']);
        $this->assertGreaterThan(0, $result[0]['similarity']);
    }

    public function test_search_similar_filters_by_category(): void
    {
        $cat1 = KnowledgeBaseCategory::create(['nama' => 'Cat 1']);
        $cat2 = KnowledgeBaseCategory::create(['nama' => 'Cat 2']);

        $doc1 = KnowledgeBaseDocument::create([
            'judul' => 'Doc 1', 'file_path' => 'd1.pdf',
            'file_size' => 100, 'status' => 'active', 'category_id' => $cat1->id,
        ]);
        $doc2 = KnowledgeBaseDocument::create([
            'judul' => 'Doc 2', 'file_path' => 'd2.pdf',
            'file_size' => 200, 'status' => 'active', 'category_id' => $cat2->id,
        ]);

        KnowledgeBaseChunk::create([
            'document_id' => $doc1->id, 'chunk_index' => 0,
            'content' => 'Content 1', 'embedding' => [0.1, 0.2],
        ]);
        KnowledgeBaseChunk::create([
            'document_id' => $doc2->id, 'chunk_index' => 0,
            'content' => 'Content 2', 'embedding' => [0.3, 0.4],
        ]);

        $result = $this->service->searchSimilar([0.1, 0.2], $cat1->id);

        $this->assertCount(1, $result);
        $this->assertEquals('Doc 1', $result[0]['document_judul']);
    }

    public function test_search_similar_limits_to_top_k(): void
    {
        $doc = KnowledgeBaseDocument::create([
            'judul' => 'Doc', 'file_path' => 'd.pdf',
            'file_size' => 100, 'status' => 'active',
        ]);

        for ($i = 0; $i < 10; $i++) {
            KnowledgeBaseChunk::create([
                'document_id' => $doc->id, 'chunk_index' => $i,
                'content' => "Content $i", 'embedding' => [0.1, 0.2],
            ]);
        }

        $result = $this->service->searchSimilar([0.1, 0.2], null, 3);

        $this->assertCount(3, $result);
    }

    public function test_ask_uses_python_answer_when_available(): void
    {
        $doc = KnowledgeBaseDocument::create([
            'judul' => 'Doc', 'file_path' => 'd.pdf',
            'file_size' => 100, 'status' => 'active',
        ]);
        KnowledgeBaseChunk::create([
            'document_id' => $doc->id, 'chunk_index' => 0,
            'content' => 'Test content', 'embedding' => [0.1, 0.2, 0.3],
        ]);

        Http::fake([
            '127.0.0.1:5001/answer' => Http::response(['answer' => 'Python answer'], 200),
        ]);

        $result = $this->service->ask('test question');

        $this->assertEquals('Python answer', $result['answer']);
    }

    public function test_ask_uses_fallback_when_python_offline(): void
    {
        $doc = KnowledgeBaseDocument::create([
            'judul' => 'Test Doc', 'file_path' => 'd.pdf',
            'file_size' => 100, 'status' => 'active',
        ]);
        KnowledgeBaseChunk::create([
            'document_id' => $doc->id, 'chunk_index' => 0,
            'content' => 'Fallback content', 'embedding' => [0.1, 0.2, 0.3],
        ]);

        Http::fake(function () {
            throw new \Exception('Connection refused');
        });

        $result = $this->service->ask('test question');

        $this->assertStringContainsString('Ditemukan', $result['answer']);
        $this->assertStringContainsString('Test Doc', $result['answer']);
    }

    public function test_health_returns_offline_when_server_down(): void
    {
        Http::fake(function () {
            throw new \Exception('Connection refused');
        });

        $health = $this->service->health();

        $this->assertEquals('offline', $health['status']);
    }
}
