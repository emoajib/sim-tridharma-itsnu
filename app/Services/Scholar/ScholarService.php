<?php

namespace App\Services\Scholar;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScholarService
{
    private const SEMANTIC_SCHOLAR_API = 'https://api.semanticscholar.org/graph/v1';

    public function __construct(
        private readonly int $timeout = 30,
        private readonly int $maxResults = 50,
    ) {}

    public function fetchByNamaProdi(string $nama, string $prodi = ''): ?array
    {
        $query = trim($nama);
        if ($prodi) {
            $query .= " {$prodi}";
        }

        try {
            $response = Http::timeout($this->timeout)
                ->get(self::SEMANTIC_SCHOLAR_API . '/paper/search', [
                    'query' => $query,
                    'limit' => $this->maxResults,
                    'fields' => 'title,authors,venue,year,externalIds,url,citationCount,publicationTypes',
                ]);

            if (!$response->successful()) {
                Log::warning('Semantic Scholar API error: ' . $response->status() . ' for query: ' . $query);
                return null;
            }

            $data = $response->json();

            return array_map(function ($paper) {
                return [
                    'id' => $paper['paperId'] ?? null,
                    'title' => $paper['title'] ?? 'Unknown',
                    'authors' => $paper['authors'] ?? [],
                    'venue' => $paper['venue'] ?? null,
                    'year' => $paper['year'],
                    'externalIds' => $paper['externalIds'] ?? [],
                    'doi' => $paper['externalIds']?->DOI ?? null,
                    'url' => $paper['url'] ?? null,
                    'citationCount' => $paper['citationCount'] ?? 0,
                    'publicationTypes' => $paper['publicationTypes'] ?? [],
                ];
            }, $data['data'] ?? []);
        } catch (\Throwable $e) {
            Log::error('Semantic Scholar API exception: ' . $e->getMessage());
            return null;
        }
    }
}
