<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KnowledgeBase\AskRequest;
use App\Http\Requests\KnowledgeBase\UploadRequest;
use App\Models\KnowledgeBaseChunk;
use App\Models\KnowledgeBaseDocument;
use App\Services\AI\EmbeddingService;
use App\Services\KnowledgeBase\DocumentProcessingService;
use App\Services\KnowledgeBase\KnowledgeBaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class KnowledgeBaseController extends Controller
{
    public function __construct(
        protected KnowledgeBaseService $knowledgeBase,
    ) {}

    public function index()
    {
        return Inertia::render('Admin/KnowledgeBase/Index', [
            'documents' => $this->knowledgeBase->getPaginatedDocuments(),
            'categories' => $this->knowledgeBase->getCategories(),
        ]);
    }

    public function upload(UploadRequest $request)
    {
        $document = $this->knowledgeBase->createDocument($request->validated(), $request->file('file'));
        $result = app(DocumentProcessingService::class)->process($document);

        if (! $result['success']) {
            return back()->with('warning', 'Dokumen terupload tetapi teks tidak bisa diekstrak.');
        }

        return back()->with('success', "Dokumen '{$document->judul}' berhasil diproses ({$result['chunk_count']} chunk).");
    }

    public function update(UploadRequest $request, KnowledgeBaseDocument $knowledgeBaseDocument)
    {
        // Use the same request validation but ignore file requirement if not provided
        $data = $request->validated();

        $knowledgeBaseDocument->update([
            'judul' => $data['judul'],
            'sumber' => $data['sumber'] ?? $knowledgeBaseDocument->sumber,
            'category_id' => $data['category_id'] ?? $knowledgeBaseDocument->category_id,
        ]);

        return back()->with('success', "Dokumen '{$knowledgeBaseDocument->judul}' berhasil diperbarui.");
    }

    public function reindex(KnowledgeBaseDocument $knowledgeBaseDocument)
    {
        $processor = app(DocumentProcessingService::class);

        try {
            $result = $processor->reprocess($knowledgeBaseDocument);

            return back()->with('success', "Re-index '{$knowledgeBaseDocument->judul}' berhasil ({$result['chunk_count']} chunk).");
        } catch (\Exception $e) {
            $knowledgeBaseDocument->update(['status' => 'error']);

            return back()->with('error', 'Gagal re-index: '.$e->getMessage());
        }
    }

    public function destroy(KnowledgeBaseDocument $knowledgeBaseDocument)
    {
        $this->knowledgeBase->deleteDocument($knowledgeBaseDocument);

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function getChunks(KnowledgeBaseDocument $knowledgeBaseDocument)
    {
        $chunks = $knowledgeBaseDocument->chunks()
            ->orderBy('chunk_index', 'asc')
            ->get(['id', 'chunk_index', 'content']);

        return response()->json([
            'document' => $knowledgeBaseDocument->only(['id', 'judul']),
            'chunks' => $chunks,
        ]);
    }

    public function updateChunk(Request $request, KnowledgeBaseChunk $knowledgeBaseChunk)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $content = $request->input('content');

        // Re-embed if content changed
        $embedding = null;
        if ($content !== $knowledgeBaseChunk->content) {
            try {
                $embeddings = app(EmbeddingService::class)->embed([$content]);
                $embedding = $embeddings[0] ?? null;
            } catch (\Exception $e) {
                Log::warning('KnowledgeBase: embedding gagal saat update chunk', [
                    'chunk_id' => $knowledgeBaseChunk->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $knowledgeBaseChunk->update([
            'content' => $content,
            'embedding' => $embedding ?? $knowledgeBaseChunk->embedding,
        ]);

        return response()->json(['success' => true, 'message' => 'Chunk berhasil diperbarui.']);
    }

    public function ask(AskRequest $request)
    {
        try {
            $question = $request->validated()['question'];
            Log::info('KnowledgeBase: ask request', ['question' => $question]);

            $result = $this->knowledgeBase->askQuestion($question, $request->validated()['category_id'] ?? null);

            if (isset($result['error'])) {
                Log::error('KnowledgeBase: ask error from service', ['error' => $result['error']]);

                return response()->json($result, 500);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('KnowledgeBase: ask exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Internal Server Error: '.$e->getMessage()], 500);
        }
    }

    public function status()
    {
        return response()->json($this->knowledgeBase->getStatus());
    }
}
