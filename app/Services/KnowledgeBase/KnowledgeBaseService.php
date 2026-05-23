<?php

namespace App\Services\KnowledgeBase;

use App\Models\KnowledgeBaseCategory;
use App\Models\KnowledgeBaseChunk;
use App\Models\KnowledgeBaseDocument;
use App\Services\MCP\MCPClientService;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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

    private function parseVector($response): ?array
    {
        if (is_array($response) && isset($response[0]) && is_array($response[0]) && isset($response[0]['text'])) {
            return array_map(function($item) {
                return (float) (is_array($item) ? ($item['text'] ?? 0) : $item);
            }, $response);
        }
        
        if (is_array($response) && isset($response['result'])) {
            return $this->parseVector($response['result']);
        }
        
        if (is_array($response) && isset($response[0]) && is_array($response[0])) {
            return $this->parseVector($response[0]);
        }

        return is_array($response) ? array_map('floatval', $response) : null;
    }

    public function askQuestion(string $question, ?int $categoryId = null): array
    {
        try {
            // 1. Get embedding for the question
            $embedding = null;
            try {
                $response = $this->mcpClient->embedText($question);
                $embedding = $this->parseVector($response);
            } catch (\Exception $e) {
                Log::warning('KnowledgeBase: Embedding failed, falling back to latest retrieval', ['error' => $e->getMessage()]);
            }

            // 2. Fetch chunks (Vector Search if embedding exists, else latest)
            $query = KnowledgeBaseChunk::with('document')
                ->when($categoryId, function ($q) use ($categoryId) {
                    $q->whereHas('document', fn ($d) => $d->where('category_id', $categoryId));
                });

            if ($embedding && is_array($embedding)) {
                $vectorStr = '[' . implode(',', $embedding) . ']';
                $chunks = $query->select('*')
                    ->selectRaw('embedding <=> ?::vector as distance', [$vectorStr])
                    ->orderBy('distance', 'asc')
                    ->take(5)
                    ->get();
            } else {
                $chunks = $query->latest()->take(10)->get();
            }

            $formattedChunks = $chunks->map(fn ($c) => [
                'content' => $c->content,
                'document_judul' => $c->document->judul ?? 'Dokumen Tanpa Judul',
                'document_sumber' => $c->document->sumber ?? 'Sumber Internal',
            ])->toArray();

            return $this->mcpClient->askRAG($question, $formattedChunks);
        } catch (\Exception $e) {
            Log::error('KnowledgeBase: askQuestion failed', ['error' => $e->getMessage()]);
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
