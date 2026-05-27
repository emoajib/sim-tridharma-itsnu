<?php

namespace Tests\Feature\Services;

use App\Models\KnowledgeBaseCategory;
use App\Models\KnowledgeBaseChunk;
use App\Models\KnowledgeBaseDocument;
use App\Services\KnowledgeBase\DocumentProcessingService;
use App\Services\KnowledgeBase\KnowledgeBaseService;
use App\Services\MCP\MCPClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KnowledgeBaseServiceTest extends TestCase
{
    use RefreshDatabase;

    private KnowledgeBaseService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $processor = $this->createMock(DocumentProcessingService::class);
        $mcpClient = $this->createMock(MCPClientService::class);

        $this->service = new KnowledgeBaseService($processor, $mcpClient);
    }

    public function test_get_paginated_documents_returns_empty_when_no_docs(): void
    {
        $result = $this->service->getPaginatedDocuments();

        $this->assertCount(0, $result->items());
    }

    public function test_get_paginated_documents_returns_documents(): void
    {
        KnowledgeBaseDocument::create([
            'judul' => 'Test Doc', 'status' => 'active',
            'file_path' => 'test.pdf', 'file_size' => 1000,
        ]);

        $result = $this->service->getPaginatedDocuments();

        $this->assertCount(1, $result->items());
        $this->assertEquals('Test Doc', $result->items()[0]->judul);
    }

    public function test_get_categories_returns_all(): void
    {
        KnowledgeBaseCategory::create(['nama' => 'Akreditasi']);
        KnowledgeBaseCategory::create(['nama' => 'Kurikulum']);

        $categories = $this->service->getCategories();

        $this->assertCount(2, $categories);
    }

    public function test_create_document_stores_file_and_record(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('dokumen.pdf', 1024);

        $doc = $this->service->createDocument([
            'judul' => 'Dokumen Baru',
            'sumber' => 'SINTA',
        ], $file);

        $this->assertNotNull($doc->id);
        $this->assertEquals('Dokumen Baru', $doc->judul);
        $this->assertEquals('draft', $doc->status);
        Storage::disk('public')->assertExists($doc->file_path);
    }

    public function test_create_document_with_category(): void
    {
        Storage::fake('public');
        $category = KnowledgeBaseCategory::create(['nama' => 'Akreditasi']);
        $file = UploadedFile::fake()->create('dokumen.pdf', 1024);

        $doc = $this->service->createDocument([
            'judul' => 'Dokumen',
            'category_id' => $category->id,
        ], $file);

        $this->assertEquals($category->id, $doc->category_id);
    }

    public function test_delete_document_removes_file_and_record(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('dokumen.pdf', 1024);
        $path = $file->store('knowledge-base', 'public');

        $document = KnowledgeBaseDocument::create([
            'judul' => 'Test', 'file_path' => $path,
            'file_size' => 1024, 'status' => 'active',
        ]);

        $this->service->deleteDocument($document);

        $this->assertDatabaseMissing('knowledge_base_documents', ['id' => $document->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_ask_question_returns_error_on_exception(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('pgvector operator requires PostgreSQL');
        }

        $processor = $this->createMock(DocumentProcessingService::class);
        $mcpClient = $this->createMock(MCPClientService::class);
        $mcpClient->method('askRAG')->willThrowException(new \Exception('Server down'));
        $service = new KnowledgeBaseService($processor, $mcpClient);

        $result = $service->askQuestion('test question');

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Gagal', $result['error']);
    }

    public function test_get_status_returns_counts(): void
    {
        $doc = KnowledgeBaseDocument::create([
            'judul' => 'D1', 'file_path' => 'd1.pdf',
            'file_size' => 100, 'status' => 'active',
        ]);
        KnowledgeBaseDocument::create([
            'judul' => 'D2', 'file_path' => 'd2.pdf',
            'file_size' => 200, 'status' => 'draft',
        ]);
        KnowledgeBaseChunk::create([
            'document_id' => $doc->id, 'chunk_index' => 0,
            'content' => 'test', 'embedding' => [0.1],
        ]);

        $status = $this->service->getStatus();

        $this->assertEquals(2, $status['documents']['total']);
        $this->assertEquals(1, $status['documents']['active']);
        $this->assertEquals(1, $status['chunks']);
    }
}
