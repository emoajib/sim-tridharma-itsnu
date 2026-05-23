<?php

namespace App\Services\KnowledgeBase;

use App\Models\KnowledgeBaseCategory;
use App\Models\KnowledgeBaseChunk;
use App\Models\KnowledgeBaseDocument;
use App\Services\MCP\MCPClientService;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class KnowledgeBaseService
{
    public function __construct(
        protected DocumentProcessingService $processor,
        protected MCPClientService $mcpClient,
    ) {}

    public function getPaginatedDocuments(): LengthAwarePaginator
    {
        return KnowledgeBaseDocument::with('category')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    public function getCategories(): Collection
    {
        return KnowledgeBaseCategory::all();
    }

    public function createDocument(array $data, UploadedFile $file): KnowledgeBaseDocument
    {
        $filePath = $file->store('knowledge-base', 'public');

        return KnowledgeBaseDocument::create([
            'category_id' => $data['category_id'] ?? null,
            'judul' => $data['judul'],
            'sumber' => $data['sumber'] ?? null,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'status' => 'draft',
        ]);
    }

    public function deleteDocument(KnowledgeBaseDocument $document): void
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
    }

    public function askQuestion(string $question, ?int $categoryId = null): array
    {
        try {
            // Fetch relevant chunks
            // For now, using a simple latest-retrieval as vector search implementation in PHP is pending
            $chunks = KnowledgeBaseChunk::with('document')
                ->when($categoryId, function ($q) use ($categoryId) {
                    $q->whereHas('document', fn ($d) => $d->where('category_id', $categoryId));
                })
                ->latest()
                ->take(15)
                ->get()
                ->map(fn ($c) => [
                    'content' => $c->content,
                    'document_judul' => $c->document->judul ?? 'Dokumen Tanpa Judul',
                    'document_sumber' => $c->document->sumber ?? 'Sumber Internal',
                ])
                ->toArray();

            return $this->mcpClient->askRAG($question, $chunks);
        } catch (\Exception $e) {
            return [
                'error' => 'Gagal menjawab pertanyaan: '.$e->getMessage(),
            ];
        }
    }

    public function getStatus(): array
    {
        return [
            'documents' => [
                'total' => KnowledgeBaseDocument::count(),
                'active' => KnowledgeBaseDocument::where('status', 'active')->count(),
            ],
            'chunks' => KnowledgeBaseChunk::count(),
            'mcp_servers' => $this->mcpClient->healthCheck(),
        ];
    }
}
