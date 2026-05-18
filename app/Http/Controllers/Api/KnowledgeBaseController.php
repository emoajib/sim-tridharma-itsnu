<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeBaseCategory;
use App\Models\KnowledgeBaseDocument;
use App\Services\AI\ChunkerService;
use App\Services\AI\EmbeddingService;
use App\Services\AI\PDFParserService;
use App\Services\AI\RAGService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class KnowledgeBaseController extends Controller
{
    public function index()
    {
        $documents = KnowledgeBaseDocument::with('category')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $categories = KnowledgeBaseCategory::all();

        return Inertia::render('Admin/KnowledgeBase/Index', [
            'documents' => $documents,
            'categories' => $categories,
        ]);
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf|max:51200',
            'judul' => 'required|string|max:255',
            'sumber' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:knowledge_base_categories,id',
        ]);

        $file = $request->file('file');
        $filePath = $file->store('knowledge-base', 'public');
        $fileSize = $file->getSize();

        $document = KnowledgeBaseDocument::create([
            'category_id' => $validated['category_id'] ?? null,
            'judul' => $validated['judul'],
            'sumber' => $validated['sumber'],
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'status' => 'draft',
        ]);

        try {
            $parser = app(PDFParserService::class);
            $result = $parser->extractText($filePath);

            $document->update([
                'page_count' => $result['page_count'],
            ]);

            if (empty(trim($result['text']))) {
                return back()->with('warning', 'Dokumen terupload tetapi teks tidak bisa diekstrak.');
            }

            $chunker = app(ChunkerService::class);
            $chunks = $chunker->chunk($result['text']);

            $embeddingService = app(EmbeddingService::class);
            $textsForEmbedding = array_map(fn($c) => $c, $chunks);

            $embeddings = $embeddingService->embed($textsForEmbedding);

            foreach ($chunks as $i => $content) {
                $document->chunks()->create([
                    'chunk_index' => $i,
                    'content' => $content,
                    'embedding' => $embeddings[$i] ?? null,
                ]);
            }

            $document->update(['status' => 'active']);

            return back()->with('success', "Dokumen '{$document->judul}' berhasil diproses (" . count($chunks) . " chunk).");
        } catch (\Exception $e) {
            $document->update(['status' => 'error']);
            return back()->with('error', 'Gagal memproses dokumen: ' . $e->getMessage());
        }
    }

    public function reindex(KnowledgeBaseDocument $knowledgeBaseDocument)
    {
        try {
            $parser = app(PDFParserService::class);
            $result = $parser->extractText($knowledgeBaseDocument->file_path);

            $knowledgeBaseDocument->chunks()->delete();
            $knowledgeBaseDocument->update(['page_count' => $result['page_count']]);

            $chunker = app(ChunkerService::class);
            $chunks = $chunker->chunk($result['text']);

            $embeddingService = app(EmbeddingService::class);
            $embeddings = $embeddingService->embed($chunks);

            foreach ($chunks as $i => $content) {
                $knowledgeBaseDocument->chunks()->create([
                    'chunk_index' => $i,
                    'content' => $content,
                    'embedding' => $embeddings[$i] ?? null,
                ]);
            }

            $knowledgeBaseDocument->update(['status' => 'active']);

            return back()->with('success', "Re-index '{$knowledgeBaseDocument->judul}' berhasil (" . count($chunks) . " chunk).");
        } catch (\Exception $e) {
            $knowledgeBaseDocument->update(['status' => 'error']);
            return back()->with('error', 'Gagal re-index: ' . $e->getMessage());
        }
    }

    public function destroy(KnowledgeBaseDocument $knowledgeBaseDocument)
    {
        Storage::disk('public')->delete($knowledgeBaseDocument->file_path);
        $knowledgeBaseDocument->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function status()
    {
        $totalDocs = KnowledgeBaseDocument::count();
        $activeDocs = KnowledgeBaseDocument::where('status', 'active')->count();
        $totalChunks = \App\Models\KnowledgeBaseChunk::count();

        $health = null;
        try {
            $health = app(RAGService::class)->health();
        } catch (\Exception $e) {
            $health = ['status' => 'offline'];
        }

        return response()->json([
            'documents' => ['total' => $totalDocs, 'active' => $activeDocs],
            'chunks' => $totalChunks,
            'ai_service' => $health,
        ]);
    }

    public function ask(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:1000',
            'category_id' => 'nullable|exists:knowledge_base_categories,id',
        ]);

        $rag = app(RAGService::class);
        $result = $rag->ask($validated['question'], $validated['category_id'] ?? null);

        return response()->json($result);
    }
}
