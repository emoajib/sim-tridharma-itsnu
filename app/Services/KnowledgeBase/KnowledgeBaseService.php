<?php

namespace App\Services\KnowledgeBase;

use App\Models\KnowledgeBaseCategory;
use App\Models\KnowledgeBaseChunk;
use App\Models\KnowledgeBaseDocument;
use App\Models\Setting;
use App\Services\MCP\MCPClientService;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
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
        $apiKey = Setting::get('gemini_api_key') ?? env('GEMINI_API_KEY');
        $model = Setting::get('gemini_model', 'gemini-1.5-flash');

        if (! $apiKey) {
            return null;
        }

        try {
            $response = Http::timeout(20)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'temperature' => 0.8,
                    'maxOutputTokens' => 1024,
                ],
            ]);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text');
                return $text ? trim($text) : null;
            }
            
            if ($response->status() === 429) {
                Log::warning('Gemini API Quota Exceeded (429)');
            } else {
                Log::warning('Gemini API Error', ['status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error('Gemini call failed', ['error' => $e->getMessage()]);
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

            // 2. Fetch chunks (Vector Search)
            $query = KnowledgeBaseChunk::with('document')
                ->when($categoryId, function ($q) use ($categoryId) {
                    $q->whereHas('document', fn ($d) => $d->where('category_id', $categoryId));
                });

            if ($embedding && is_array($embedding)) {
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

            // 3. Synthesize Answer using Gemini
            $context = $chunks->map(fn ($c) => "DOKUMEN: {$c->document->judul}\nSUMBER: {$c->document->sumber}\nISI: {$c->content}")->implode("\n\n---\n\n");
            
            $prompt = "Anda adalah Asisten Akreditasi ITSNU Pekalongan bernama Kilo. " .
                      "Tugas Anda: Menjawab pertanyaan pengguna berdasarkan potongan dokumen (CONTEXT) yang disediakan.\n\n" .
                      "INSTRUKSI PENTING:\n" .
                      "1. Jawab dengan gaya bahasa yang HUMANIS (seperti berbicara dengan teman sejawat) namun tetap PROFESIONAL.\n" .
                      "2. Gunakan sapaan yang sopan dan paragraf yang mengalir lancar (hindari daftar poin yang membosankan jika tidak perlu).\n" .
                      "3. Jika jawaban ada di CONTEXT, jelaskan secara detail.\n" .
                      "4. Jika jawaban TIDAK ADA di CONTEXT, gunakan pengetahuan umum Anda tentang akreditasi Indonesia untuk membantu, " .
                         "TAPI beri tahu pengguna dengan jujur bahwa informasi ini bersifat umum karena tidak ditemukan di dokumen internal kampus.\n" .
                      "5. Jika pertanyaan tidak relevan dengan akreditasi/kampus, jawab dengan ramah dan arahkan kembali ke topik akreditasi.\n\n" .
                      "CONTEXT:\n{$context}\n\n" .
                      "PERTANYAAN: {$question}";

            $answer = $this->callGemini($prompt);

            if (! $answer) {
                // Improved Fallback Format
                $formatted = "Halo! Mohon maaf, saat ini fitur kecerdasan buatan (Gemini) sedang mencapai batas kuota harian.\n\n" .
                             "Namun, saya menemukan beberapa informasi yang mungkin relevan dari dokumen kami:\n\n";
                
                foreach ($chunks as $c) {
                    $formatted .= "• ". substr($c->content, 0, 200) . "...\n";
                }
                
                $formatted .= "\nSilakan hubungi administrator jika Anda memerlukan jawaban yang lebih detail.";

                return [
                    'answer' => $formatted,
                    'sources' => $chunks->map(fn ($c) => [
                        'judul' => $c->document->judul,
                        'sumber' => $c->document->sumber,
                        'skor' => 0,
                    ])->unique('judul')->values()->toArray(),
                    'mode' => 'fallback-manual'
                ];
            }

            return [
                'answer' => $answer,
                'sources' => $chunks->map(fn ($c) => [
                    'judul' => $c->document->judul,
                    'sumber' => $c->document->sumber,
                    'skor' => isset($c->distance) ? round((1 - $c->distance) * 100, 1) : 0,
                ])->unique('judul')->values()->toArray(),
                'mode' => 'generative-rag',
            ];
        } catch (\Exception $e) {
            Log::error('KnowledgeBase: askQuestion failed', ['error' => $e->getMessage()]);

            return ['error' => 'Waduh, sepertinya ada sedikit kendala teknis: '.$e->getMessage()];
        }
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
