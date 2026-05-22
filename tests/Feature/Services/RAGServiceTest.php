<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace Tests\Feature\Services;

use App\Models\KnowledgeBaseChunk;
use App\Models\KnowledgeBaseDocument;
use App\Models\User;
use App\Services\AI\EmbeddingService;
use App\Services\AI\RAGService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RAGServiceTest extends TestCase
{
    use RefreshDatabase;

    private RAGService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create(['id' => 1]);
        Auth::login($user);

        $embedding = $this->createMock(EmbeddingService::class);
        $embedding->method('embedText')->willReturn([0.1, 0.2, 0.3]);
        app()->instance(EmbeddingService::class, $embedding);

        $this->service = app(RAGService::class);
    }

    public function test_ask_returns_guardrail_message_when_no_chunks(): void
    {
        $result = $this->service->ask('test question');

        $this->assertStringContainsString('tidak menemukan informasi yang cukup relevan', $result['answer']);
        $this->assertEmpty($result['sources']);
        $this->assertEquals('no-context', $result['mode']);

        $this->assertDatabaseHas('trx_chat_history', [
            'question' => 'test question',
            'mode' => 'no-context',
        ]);
    }

    public function test_ask_returns_guardrail_message_when_similarity_low(): void
    {
        $doc = KnowledgeBaseDocument::create([
            'judul' => 'Doc', 'file_path' => 'd.pdf', 'file_size' => 100, 'status' => 'active',
        ]);
        KnowledgeBaseChunk::create([
            'document_id' => $doc->id, 'chunk_index' => 0,
            'content' => 'Low relevance content',
            'embedding' => [0.9, -0.9, 0.1], // Very different from [0.1, 0.2, 0.3]
        ]);

        $result = $this->service->ask('test question');

        $this->assertStringContainsString('tidak menemukan informasi yang cukup relevan', $result['answer']);
        $this->assertLessThan(0.65, $result['max_similarity']);
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
            '127.0.0.1:5001/answer' => Http::response(['answer' => 'Python answer', 'mode' => 'qa-extractive'], 200),
        ]);

        $result = $this->service->ask('test question');

        $this->assertEquals('Python answer', $result['answer']);
        $this->assertEquals('qa-extractive', $result['mode']);
    }

    public function test_ask_saves_to_chat_history(): void
    {
        $doc = KnowledgeBaseDocument::create([
            'judul' => 'Doc', 'file_path' => 'd.pdf', 'file_size' => 100, 'status' => 'active',
        ]);
        KnowledgeBaseChunk::create([
            'document_id' => $doc->id, 'chunk_index' => 0,
            'content' => 'Relevant content', 'embedding' => [0.1, 0.2, 0.3],
        ]);

        Http::fake([
            '127.0.0.1:5001/answer' => Http::response(['answer' => 'AI Response'], 200),
        ]);

        $this->service->ask('Bagaimana IKU 1?');

        $this->assertDatabaseHas('trx_chat_history', [
            'question' => 'Bagaimana IKU 1?',
            'answer' => 'AI Response',
        ]);
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
}
