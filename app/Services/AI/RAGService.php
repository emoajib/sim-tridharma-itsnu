<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Services\AI;

use App\Models\ChatHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RAGService
{
    /**
     * Threshold minimal relevansi (0.0 - 1.0)
     * Jika di bawah ini, AI akan menolak menjawab untuk menghindari halusinasi.
     */
    const SIMILARITY_THRESHOLD = 0.65;

    protected string $baseUrl;

    protected EmbeddingService $embedding;

    public function __construct()
    {
        $this->baseUrl = config('ai-service.base_url', 'http://127.0.0.1:5001');
        $this->embedding = app(EmbeddingService::class);
    }

    public function ask(string $question, ?int $categoryId = null, int $topK = 5): array
    {
        try {
            $chunks = $this->searchSimilar($question, $categoryId, $topK);

            $maxSimilarity = ! empty($chunks) ? $chunks[0]['similarity'] : 0;
            $mode = 'sentence-only';
            $answer = '';

            // Guardrail: Cek apakah relevansi mencukupi
            if (empty($chunks) || $maxSimilarity < self::SIMILARITY_THRESHOLD) {
                $answer = 'Maaf, saya tidak menemukan informasi yang cukup relevan dalam pedoman ITSNU untuk menjawab pertanyaan tersebut. Silakan hubungi unit terkait untuk informasi lebih lanjut.';
                $mode = 'no-context';
                $chunks = []; // Kosongkan chunks agar tidak tampil di sumber
            } else {
                // Panggil AI Generation (Python Service)
                $pythonResponse = $this->askPythonAnswer($question, $chunks);
                if ($pythonResponse) {
                    $answer = $pythonResponse['answer'];
                    $mode = $pythonResponse['mode'] ?? 'qa-extractive';
                } else {
                    $answer = $this->formatFallback($chunks);
                }
            }

            $sources = array_map(fn ($c) => [
                'judul' => $c['document_judul'],
                'sumber' => $c['document_sumber'],
                'skor' => round($c['similarity'] * 100, 1),
            ], $chunks);

            // Simpan ke Riwayat Chat (Analitik LPM)
            $history = ChatHistory::create([
                'user_id' => Auth::id() ?? 1, // Default ke ID 1 jika tidak login (untuk sistem/guest)
                'question' => $question,
                'answer' => $answer,
                'sources' => array_unique($sources, SORT_REGULAR),
                'max_similarity' => $maxSimilarity,
                'mode' => $mode,
            ]);

            return [
                'id' => $history->id,
                'answer' => $answer,
                'sources' => $history->sources,
                'max_similarity' => $maxSimilarity,
                'mode' => $mode,
            ];

        } catch (\Exception $e) {
            Log::error('RAGService Error', ['message' => $e->getMessage()]);

            return [
                'answer' => 'Terjadi kesalahan teknis pada layanan asisten AI. Silakan coba beberapa saat lagi.',
                'sources' => [],
                'error' => true,
            ];
        }
    }

    public function searchSimilar(string $query, int $topK = 5, ?int $categoryId = null): array
    {
        $embedding = $this->embedding->embedText($query);
        $embeddingStr = '[' . implode(',', $embedding) . ']';

        $query = DB::table('knowledge_base_chunks')
            ->select(
                'knowledge_base_chunks.id',
                'knowledge_base_chunks.content',
                'knowledge_base_documents.judul as document_judul',
                'knowledge_base_documents.sumber as document_sumber'
            )
            ->selectRaw('knowledge_base_chunks.embedding <=> ?::vector as distance', [$embeddingStr])
            ->join('knowledge_base_documents', 'knowledge_base_chunks.document_id', '=', 'knowledge_base_documents.id')
            ->whereNotNull('knowledge_base_chunks.embedding')
            ->where('knowledge_base_documents.status', 'active')
            ->orderBy('distance')
            ->limit($topK);

        if ($categoryId) {
            $query->where('knowledge_base_documents.category_id', $categoryId);
        }

        return $query->get()->map(function ($row) {
            return [
                'id' => $row->id,
                'content' => $row->content,
                'similarity' => 1 - (float) $row->distance,
                'document_judul' => $row->document_judul ?? 'Unknown',
                'document_sumber' => $row->document_sumber ?? '',
            ];
        })->toArray();
    }

    protected function askPythonAnswer(string $question, array $chunks): ?array
    {
        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/answer", [
                'question' => $question,
                'chunks' => $chunks,
                'top_k' => 5,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Python /answer failed', ['status' => $response->status()]);
        } catch (\Exception $e) {
            Log::warning('Python /answer error', ['message' => $e->getMessage()]);
        }

        return null;
    }

    protected function formatFallback(array $chunks): string
    {
        $parts = [];
        foreach ($chunks as $chunk) {
            $sim = round($chunk['similarity'] * 100, 1);
            $parts[] = "📄 **{$chunk['document_judul']}** (Relevansi {$sim}%):\n{$chunk['content']}";
        }

        return "Berdasarkan dokumen internal yang ditemukan:\n\n".implode("\n\n---\n\n", $parts);
    }
}
