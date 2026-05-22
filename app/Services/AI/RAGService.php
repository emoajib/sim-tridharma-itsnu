<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Services\AI;

use App\Models\ChatHistory;
use App\Models\KnowledgeBaseChunk;
use Illuminate\Support\Facades\Auth;
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
            $questionVector = $this->embedding->embedText($question);
            $chunks = $this->searchSimilar($questionVector, $categoryId, $topK);

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

    public function searchSimilar(array $vector, ?int $categoryId = null, int $topK = 5): array
    {
        $query = KnowledgeBaseChunk::query()
            ->select('knowledge_base_chunks.*')
            ->join('knowledge_base_documents', 'knowledge_base_chunks.document_id', '=', 'knowledge_base_documents.id')
            ->whereNotNull('knowledge_base_chunks.embedding')
            ->where('knowledge_base_documents.status', 'active');

        if ($categoryId) {
            $query->where('knowledge_base_documents.category_id', $categoryId);
        }

        $chunks = $query->get();

        $scored = [];
        foreach ($chunks as $chunk) {
            $chunkVector = $chunk->embedding;
            if (! $chunkVector) {
                continue;
            }

            $similarity = $this->cosineSimilarity($vector, $chunkVector);
            $scored[] = [
                'id' => $chunk->id,
                'content' => $chunk->content,
                'similarity' => $similarity,
                'document_judul' => $chunk->document->judul ?? 'Unknown',
                'document_sumber' => $chunk->document->sumber ?? '',
            ];
        }

        usort($scored, fn ($a, $b) => $b['similarity'] <=> $a['similarity']);

        return array_slice($scored, 0, $topK);
    }

    protected function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0;
        $normA = 0;
        $normB = 0;
        $len = min(count($a), count($b));

        for ($i = 0; $i < $len; $i++) {
            $dot += (float) $a[$i] * (float) $b[$i];
            $normA += (float) $a[$i] * (float) $a[$i];
            $normB += (float) $b[$i] * (float) $b[$i];
        }

        $denom = sqrt($normA) * sqrt($normB);

        return $denom > 0 ? $dot / $denom : 0;
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
