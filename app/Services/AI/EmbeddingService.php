<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('ai-service.base_url', 'http://127.0.0.1:5001');
    }

    public function embed(array $texts): array
    {
        $response = Http::timeout(60)->post("{$this->baseUrl}/embed", [
            'texts' => $texts,
        ]);

        if ($response->failed()) {
            Log::error('AI Service embed failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Gagal mendapatkan embedding dari AI Service');
        }

        return $response->json()['embeddings'];
    }

    public function embedText(string $text): array
    {
        $embeddings = $this->embed([$text]);
        return $embeddings[0];
    }

    public function health(): array
    {
        $response = Http::timeout(5)->get("{$this->baseUrl}/health");
        return $response->json();
    }
}
