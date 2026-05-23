<?php

namespace App\Services\KnowledgeBase;

use App\Models\KnowledgeBaseCategory;
use App\Models\KnowledgeBaseChunk;
use App\Models\KnowledgeBaseDocument;
use App\Models\SemanticCache;
use App\Models\Setting;
use App\Services\MCP\MCPClientService;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Vetted by AI - Manual Review Required by Senior Engineer/Manager
 */
class KnowledgeBaseService
{
    // Threshold for semantic similarity (0.95 = 95% similarity)
    const CACHE_SIMILARITY_THRESHOLD = 0.95;

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

        /** @var KnowledgeBaseDocument $doc */
        $doc = KnowledgeBaseDocument::create([
            'category_id' => $data['category_id'] ?? null,
            'judul' => $data['judul'],
            'sumber' => $data['sumber'] ?? null,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'status' => 'draft',
        ]);

        return $doc;
    }

    public function deleteDocument(KnowledgeBaseDocument $document): void
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
    }

    private function detectGreeting(string $text): bool
    {
        $greetings = [
            'hi', 'halo', 'hello', 'assalam', 'pagi', 'siang', 'sore', 'malam', 
            'tes', 'test', 'siapa', 'apa kabar', 'p', 'hey', 'punten', 'sampurasun'
        ];
        $text = strtolower($text);
        foreach ($greetings as $g) {
            if (str_contains($text, $g) && strlen($text) < 40) {
                return true;
            }
        }

        return false;
    }

    private function callGemini(string $prompt): ?string
    {
        $apiKey = Setting::get('gemini_api_key') ?? config('ai-service.gemini.api_key');
        $model = Setting::get('gemini_model', config('ai-service.gemini.model', 'gemini-1.5-flash'));

        if (! $apiKey) {
            return null;
        }

        try {
            $response = Http::timeout(25)->post("https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 1024,
                ],
            ]);

            if ($response->successful()) {
                /** @var string|null $text */
                $text = $response->json('candidates.0.content.parts.0.text');
                return $text ? trim($text) : null;
            }
            
            if ($response->status() === 429) {
                Log::warning('Gemini API Quota Exceeded (429), checking for failover...');
            } else {
                Log::warning('Gemini API Error', [
                    'status' => $response->status(), 
                    'model' => $model,
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Gemini call failed', ['error' => $e->getMessage()]);
        }

        // FAILOVER TO OPENAI
        return $this->callOpenAI($prompt);
    }

    private function callOpenAI(string $prompt): ?string
    {
        $apiKey = Setting::get('openai_api_key') ?? config('ai-service.openai.api_key');
        $model = Setting::get('openai_model', config('ai-service.openai.model', 'gpt-3.5-turbo'));
        $baseUrl = Setting::get('openai_base_url', config('ai-service.openai.base_url', 'https://api.openai.com/v1'));

        if (! $apiKey) {
            return null;
        }

        try {
            Log::info('Attempting OpenAI failover...');
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post("{$baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 1024,
            ]);

            if ($response->successful()) {
                /** @var string|null $text */
                $text = $response->json('choices.0.message.content');
                return $text ? trim($text) : null;
            }

            Log::warning('OpenAI API Error during failover', ['status' => $response->status(), 'body' => $response->body()]);
        } catch (\Exception $e) {
            Log::error('OpenAI failover failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public function askQuestion(string $question, ?int $categoryId = null): array
    {
        try {
            // 0. Detect Greeting / Small Talk
            if ($this->detectGreeting($question)) {
                $prompt = "Anda adalah 'Kilo', Asisten Digital Akreditasi ITSNU Pekalongan yang sangat ramah, humanist, dan cerdas. " .
                          "Sapa pengguna dengan hangat dalam Bahasa Indonesia. Beritahu mereka bahwa Anda bisa membantu menjelaskan kebijakan akreditasi BAN-PT/LAM, " .
                          "prosedur tridharma dosen, atau data teknis lainnya yang ada di sistem ini. " .
                          "Gunakan gaya bahasa santai tapi sopan (ala asisten modern). User menyapa: \"{$question}\"";
                
                $greet = $this->callGemini($prompt);
                if ($greet) {
                    return ['answer' => $greet, 'sources' => [], 'mode' => 'greeting'];
                }
            }

            // 1. Get embedding for the question
            $embedding = null;
            try {
                $response = $this->mcpClient->embedText($question);
                $embedding = $this->parseVector($response);
            } catch (\Exception $e) {
                Log::warning('KnowledgeBase: Embedding failed', ['error' => $e->getMessage()]);
            }

            // 2. CHECK SEMANTIC CACHE FIRST
            if (is_array($embedding)) {
                $vectorStr = '['.implode(',', $embedding).']';
                $cached = SemanticCache::select('*')
                    ->selectRaw('question_vector <=> ?::vector as distance', [$vectorStr])
                    ->whereRaw('question_vector <=> ?::vector < ?', [$vectorStr, 1 - self::CACHE_SIMILARITY_THRESHOLD])
                    ->orderBy('distance', 'asc')
                    ->first();

                if ($cached instanceof SemanticCache) {
                    Log::info('Semantic Cache Hit', ['question' => $question, 'cached_id' => $cached->id]);
                    $cached->increment('hit_count');
                    $cached->update(['last_hit_at' => now()]);
                    
                    return [
                        'answer' => $cached->answer . "\n\n*(Jawaban diambil dari Smart Cache)*",
                        'sources' => $cached->sources ?? [],
                        'mode' => 'semantic-cache-hit',
                    ];
                }
            }

            // 3. Fetch chunks (Vector Search)
            $query = KnowledgeBaseChunk::with('document')
                ->when($categoryId, function ($q) use ($categoryId) {
                    $q->whereHas('document', fn ($d) => $d->where('category_id', $categoryId));
                });

            if (is_array($embedding)) {
                $vectorStr = '['.implode(',', $embedding).']';
                $chunks = $query->select('*')
                    ->selectRaw('embedding <=> ?::vector as distance', [$vectorStr])
                    ->orderBy('distance', 'asc')
                    ->take(5)
                    ->get();
            } else {
                $chunks = $query->latest()->take(5)->get();
            }

            if ($chunks->isEmpty()) {
                return [
                    'answer' => "Maaf sekali, saya belum memiliki dokumen yang relevan di pangkalan data saya untuk menjawab pertanyaan Anda tentang '{$question}'.\n\nAnda bisa mencoba mengunggah dokumen kebijakan terkait di menu Admin agar saya bisa mempelajarinya.",
                    'sources' => []
                ];
            }

            // 4. Synthesize Answer using Gemini
            /** @var Collection<int, KnowledgeBaseChunk> $chunks */
            $context = $chunks->map(function(KnowledgeBaseChunk $c) {
                /** @var KnowledgeBaseDocument|null $doc */
                $doc = $c->document;
                $judul = $doc ? $doc->judul : 'Dokumen Tanpa Judul';
                return "DOKUMEN: {$judul}\nISI: {$c->content}";
            })->implode("\n\n---\n\n");
            
            $prompt = "Anda adalah 'Kilo', Asisten Akreditasi ITSNU Pekalongan yang ramah, humanist, and ahli dalam kebijakan BAN-PT/LAM. " .
                      "Tugas Anda: Menjawab pertanyaan berdasarkan CONTEXT yang disediakan.\n\n" .
                      "ATURAN MENJAWAB:\n" .
                      "1. Awali dengan sapaan yang hangat namun profesional (Contoh: 'Halo Bapak/Ibu, terkait hal tersebut...', 'Halo Sahabat ITSNU...').\n" .
                      "2. Gunakan gaya bahasa yang MENGALIR dan HUMANIS. Hindari format poin-poin yang kaku jika bisa dijelaskan dalam paragraf yang enak dibaca.\n" .
                      "3. Jika jawaban ada di CONTEXT, jelaskan dengan detail dan berikan konteks yang relevan bagi pengguna.\n" .
                      "4. Jika jawaban TIDAK ADA di CONTEXT, beritahu dengan jujur namun tetap berikan saran umum berdasarkan pengetahuan umum akreditasi Indonesia.\n" .
                      "5. Pastikan nada bicara Anda membantu dan memberikan semangat kepada para dosen/admin prodi.\n\n" .
                      "CONTEXT:\n{$context}\n\n" .
                      "PERTANYAAN: {$question}";

            $answer = $this->callGemini($prompt);

            /** @var Collection<int, array{judul: string, sumber: string|null, skor: float}> $sourceCollection */
            $sourceCollection = $chunks->map(function(KnowledgeBaseChunk $c) {
                /** @var KnowledgeBaseDocument|null $doc */
                $doc = $c->document;
                $cArray = $c->toArray();
                $distance = isset($cArray['distance']) ? (float)$cArray['distance'] : 1.0;
                return [
                    'judul' => $doc ? $doc->judul : 'Tanpa Judul',
                    'sumber' => $doc ? $doc->sumber : null,
                    'skor' => round((1 - $distance) * 100, 1),
                ];
            });
            $sources = $sourceCollection->unique('judul')->values()->toArray();

            if (! $answer) {
                // FALLBACK: Try local RAG engine if Gemini is unavailable
                try {
                    Log::info('KnowledgeBase: Gemini unavailable, trying local RAG fallback');
                    
                    /** @var Collection<int, KnowledgeBaseChunk> $chunks */
                    $chunksForMcp = $chunks->map(function(KnowledgeBaseChunk $c) {
                        /** @var KnowledgeBaseDocument|null $doc */
                        $doc = $c->document;
                        $cArray = $c->toArray();
                        $distance = isset($cArray['distance']) ? (float)$cArray['distance'] : 1.0;
                        return [
                            'content' => $c->content,
                            'document_judul' => $doc ? $doc->judul : 'Tanpa Judul',
                            'document_sumber' => $doc ? $doc->sumber : null,
                            'similarity' => 1 - $distance,
                        ];
                    })->toArray();

                    $localResult = $this->mcpClient->askRAG($question, $chunksForMcp);
                    
                    if (isset($localResult['answer']) && strlen($localResult['answer']) > 20) {
                        $prefix = "Halo! Mohon maaf, koneksi ke pusat sedang sibuk, saya bantu jawab menggunakan database lokal saya ya.\n\n";
                        $answer = $prefix . $localResult['answer'];
                        
                        // SAVE FALLBACK TO CACHE TOO
                        if (is_array($embedding)) {
                            try {
                                SemanticCache::create([
                                    'question' => $question,
                                    'answer' => $answer,
                                    'sources' => $sources,
                                    'question_vector' => $embedding,
                                    'provider' => 'local-fallback',
                                ]);
                            } catch (\Exception $e) {
                                Log::error('Failed to save semantic cache (fallback)', ['error' => $e->getMessage()]);
                            }
                        }

                        return [
                            'answer' => $answer,
                            'sources' => $sources,
                            'mode' => 'local-rag-fallback',
                        ];
                    }
                } catch (\Exception $e) {
                    Log::warning('KnowledgeBase: Local RAG fallback failed', ['error' => $e->getMessage()]);
                }

                // ULTIMATE FALLBACK
                $formatted = "Halo! Saya mohon maaf sekali, saat ini layanan AI sedang tidak stabil.\n\n" .
                             "Berdasarkan catatan yang saya miliki, mungkin ini bisa membantu Anda:\n\n";
                
                foreach ($chunks as $c) {
                    $formatted .= "• ". $c->content . "\n\n";
                }
                
                $formatted .= "Semoga informasi singkat ini bermanfaat. Silakan hubungi admin for bantuan lebih lanjut.";

                return [
                    'answer' => $formatted,
                    'sources' => $sources,
                    'mode' => 'fallback-manual'
                ];
            }

            // 5. SAVE TO SEMANTIC CACHE
            if (is_array($embedding)) {
                try {
                    SemanticCache::create([
                        'question' => $question,
                        'answer' => $answer,
                        'sources' => $sources,
                        'question_vector' => $embedding,
                        'provider' => 'gemini',
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to save semantic cache', ['error' => $e->getMessage()]);
                }
            }

            return [
                'answer' => $answer,
                'sources' => $sources,
                'mode' => 'generative-rag',
            ];
        } catch (\Exception $e) {
            Log::error('KnowledgeBase: askQuestion failed', ['error' => $e->getMessage()]);

            return ['error' => 'Waduh, sepertinya ada sedikit kendala teknis: '.$e->getMessage()];
        }
    }

    private function parseVector(mixed $response): ?array
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

    public function getStatus(): array
    {
        return [
            'documents' => [
                'total' => KnowledgeBaseDocument::count(),
                'active' => KnowledgeBaseDocument::where('status', 'active')->count(),
            ],
            'chunks' => KnowledgeBaseChunk::count(),
            'mcp_servers' => $this->mcpClient->healthCheck(),
            'cache' => [
                'total_entries' => SemanticCache::count(),
                'total_hits' => (int) SemanticCache::sum('hit_count'),
            ]
        ];
    }
}
