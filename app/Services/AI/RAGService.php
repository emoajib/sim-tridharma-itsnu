<?php

namespace App\Services\AI;

use App\Models\KnowledgeBaseChunk;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RAGService
{
    protected string $baseUrl;
    protected EmbeddingService $embedding;

    public function __construct()
    {
        $this->baseUrl = config('ai-service.base_url', 'http://127.0.0.1:5001');
        $this->embedding = app(EmbeddingService::class);
    }

    public function ask(string $question, ?int $categoryId = null, int $topK = 5): array
    {
        $questionVector = $this->embedding->embedText($question);

        $chunks = $this->searchSimilar($questionVector, $categoryId, $topK);

        if (empty($chunks)) {
            return [
                'answer' => 'Maaf, tidak ditemukan dokumen yang relevan untuk pertanyaan Anda.',
                'sources' => [],
                'chunks_used' => 0,
            ];
        }

        $answer = $this->askPythonAnswer($question, $chunks);

        if ($answer === null) {
            $answer = $this->formatFallback($chunks);
        }

        $sources = array_map(fn($c) => [
            'judul' => $c['document_judul'],
            'sumber' => $c['document_sumber'],
            'skor' => round($c['similarity'] * 100, 1),
        ], $chunks);

        return [
            'answer' => $answer,
            'sources' => array_unique($sources, SORT_REGULAR),
            'chunks_used' => count($chunks),
        ];
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
            if (!$chunkVector) continue;

            $similarity = $this->cosineSimilarity($vector, $chunkVector);
            $scored[] = [
                'id' => $chunk->id,
                'content' => $chunk->content,
                'similarity' => $similarity,
                'document_judul' => $chunk->document->judul ?? 'Unknown',
                'document_sumber' => $chunk->document->sumber ?? '',
            ];
        }

        usort($scored, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return array_slice($scored, 0, $topK);
    }

    protected function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0;
        $normA = 0;
        $normB = 0;
        $len = min(count($a), count($b));

        for ($i = 0; $i < $len; $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        $denom = sqrt($normA) * sqrt($normB);
        return $denom > 0 ? $dot / $denom : 0;
    }

    protected function askPythonAnswer(string $question, array $chunks): ?string
    {
        try {
            $response = Http::timeout(30)->post("{$this->baseUrl}/answer", [
                'question' => $question,
                'chunks' => $chunks,
                'top_k' => 5,
            ]);

            if ($response->successful()) {
                return $response->json()['answer'];
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
        foreach ($chunks as $i => $chunk) {
            $sim = round($chunk['similarity'] * 100, 1);
            $label = match (true) {
                $chunk['similarity'] >= 0.9 => '📌',
                $chunk['similarity'] >= 0.7 => '📄',
                default => '📝',
            };
            $parts[] = "{$label} {$chunk['document_judul']} (relevansi {$sim}%)\n{$chunk['content']}";
        }

        return "Ditemukan " . count($parts) . " bagian dokumen relevan:\n\n" . implode("\n\n---\n\n", $parts);
    }

    public function health(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->json();
        } catch (\Exception $e) {
            return ['status' => 'offline', 'error' => $e->getMessage()];
        }
    }
}
