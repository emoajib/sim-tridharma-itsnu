<?php

declare(strict_types=1);

namespace App\Services\MCP;

use App\Jobs\CallMcpTool;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MCPClientService
{
    protected string $agentsUrl;
    protected string $ragUrl;
    protected string $apiKey = '';

    public function __construct()
    {
        $this->agentsUrl = config('mcp.agents_url', 'http://localhost:8001');
        $this->ragUrl = config('mcp.rag_url', 'http://localhost:5001');
        $this->apiKey = (string) config('mcp.api_key', '');

        if (empty($this->apiKey)) {
            throw new \RuntimeException('MCP API key is not configured. Set MCP_API_KEY env variable.');
        }
    }

    public function listTools(): array
    {
        try {
            $response = Http::timeout(5)->withHeaders([
                'X-API-Key' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get("{$this->agentsUrl}/api/mcp/tools");

            if ($response->successful()) {
                return $response->json('tools', []);
            }

            Log::error('MCP list tools failed', ['status' => $response->status()]);
            return [];
        } catch (Exception $e) {
            Log::error('MCP list tools exception', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Call MCP tool and extract the actual tool result from the MCP response.
     * Handles both raw JSON responses and MCP protocol wrapped responses.
     */
    public function callTool(string $toolName, array $arguments = [], string $server = 'agents'): array
    {
        $baseUrl = $server === 'rag' ? $this->ragUrl : $this->agentsUrl;

        try {
            $response = Http::timeout(30)->withHeaders([
                'X-API-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$baseUrl}/mcp/tools/call", [
                'name' => $toolName,
                'arguments' => $arguments,
            ]);

            if (!$response->successful()) {
                Log::error("MCP tool call failed: {$toolName}", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception("MCP tool call failed: {$response->status()}");
            }

            return $this->extractToolResult($response->json(), $toolName);
        } catch (Exception $e) {
            Log::error("MCP tool call exception: {$toolName}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Synchronous MCP call with polling — used by background jobs only.
     */
    public function callToolSync(string $toolName, array $arguments = [], string $server = 'agents', int $maxRetries = 10, int $retryDelay = 300): array
    {
        $baseUrl = $server === 'rag' ? $this->ragUrl : $this->agentsUrl;

        try {
            $response = Http::timeout(10)->withHeaders([
                'X-API-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$baseUrl}/mcp/tools/call", [
                'name' => $toolName,
                'arguments' => $arguments,
            ]);

            if (!$response->successful()) {
                throw new Exception("MCP tool call failed: {$response->status()}");
            }

            $raw = $response->json();

            if (isset($raw['result']['task_id']) || isset($raw['task_id'])) {
                $taskId = $raw['result']['task_id'] ?? $raw['task_id'];

                for ($i = 0; $i < $maxRetries; $i++) {
                    usleep($retryDelay * 1000);

                    $statusResponse = Http::timeout(5)->withHeaders([
                        'X-API-Key' => $this->apiKey,
                        'Accept' => 'application/json',
                    ])->get("{$baseUrl}/mcp/tasks/{$taskId}");

                    if ($statusResponse->successful()) {
                        $status = $statusResponse->json();

                        if (($status['status'] ?? '') === 'completed') {
                            return $this->extractToolResult($status['result'] ?? $status, $toolName);
                        }

                        if (($status['status'] ?? '') === 'failed') {
                            throw new Exception('MCP task failed: ' . ($status['error'] ?? 'Unknown error'));
                        }
                    }
                }

                throw new Exception("MCP task timeout after {$maxRetries} retries");
            }

            return $this->extractToolResult($raw, $toolName);
        } catch (Exception $e) {
            Log::error("MCP sync tool call exception: {$toolName}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Extract the actual tool result from MCP wrapped response.
     * MCP protocol wraps results in content[0].text as a JSON string.
     * FastAPI wrapper returns {"result": ...}.
     * Direct responses return the data directly.
     */
    private function extractToolResult(array $response, string $toolName = ''): array
    {
        if (isset($response['result']) && is_array($response['result'])) {
            $inner = $response['result'];

            if (isset($inner['content']) && is_array($inner['content']) && count($inner['content']) > 0) {
                $firstContent = $inner['content'][0];
                if (isset($firstContent['text']) && is_string($firstContent['text'])) {
                    $decoded = json_decode($firstContent['text'], true);
                    if (is_array($decoded)) {
                        return $decoded;
                    }
                }
                if (isset($firstContent['type']) && $firstContent['type'] === 'text' && isset($firstContent['text'])) {
                    $decoded = json_decode($firstContent['text'], true);
                    if (is_array($decoded)) {
                        return $decoded;
                    }
                }
                return $inner;
            }

            if (isset($inner['isError'])) {
                unset($inner['isError']);
            }
            if (isset($inner['content'])) {
                unset($inner['content']);
            }

            if (!empty($inner)) {
                return $inner;
            }

            return $response['result'];
        }

        if (isset($response['content']) && is_array($response['content']) && count($response['content']) > 0) {
            $firstContent = $response['content'][0];
            if (isset($firstContent['text']) && is_string($firstContent['text'])) {
                $decoded = json_decode($firstContent['text'], true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        if (isset($response['jsonrpc'], $response['result'])) {
            return $this->extractToolResult($response['result'], $toolName);
        }

        return $response;
    }

    /**
     * Dispatch an MCP tool call as a background job.
     */
    public function dispatchTool(string $toolName, array $arguments = [], string $server = 'agents'): array
    {
        $job = new CallMcpTool(toolName: $toolName, arguments: $arguments, server: $server);
        dispatch($job);

        $taskId = $job->getTaskId();

        Cache::put("mcp_task_{$taskId}", ['status' => 'queued'], 600);

        return [
            'status' => 'queued',
            'task_id' => $taskId,
        ];
    }

    public function getTaskStatus(string $taskId): array
    {
        $cached = Cache::get("mcp_task_{$taskId}");
        if (!$cached) {
            return ['status' => 'not_found'];
        }
        return $cached;
    }

    public function getTaskResult(string $taskId): array
    {
        $cached = Cache::get("mcp_task_{$taskId}");
        if (!$cached) {
            return ['success' => false, 'error' => 'Task not found'];
        }

        if ($cached['status'] !== 'completed') {
            return ['success' => false, 'error' => 'Task not completed', 'status' => $cached['status']];
        }

        return ['success' => true, 'result' => $cached['result']];
    }

    // ========================================================================
    // Agent Tools
    // ========================================================================

    public function runPeringatanCheck(int $prodiId): array
    {
        return $this->callToolSync('peringatan_check', ['prodi_id' => $prodiId]);
    }

    public function runRekomendasiGenerate(int $prodiId, int $topN = 10): array
    {
        return $this->callToolSync('rekomendasi_generate', [
            'prodi_id' => $prodiId,
            'top_n' => $topN,
        ]);
    }

    public function runVerifikasiDokumen(int $prodiId, ?int $docBuktiId = null): array
    {
        $arguments = ['prodi_id' => $prodiId];
        if ($docBuktiId !== null) {
            $arguments['doc_bukti_id'] = $docBuktiId;
        }
        return $this->callToolSync('verifikasi_dokumen', $arguments);
    }

    public function runPrediksiSkor(int $prodiId, ?int $periodeId = null, int $maxRetries = 10, int $retryDelay = 300): array
    {
        $arguments = ['prodi_id' => $prodiId];
        if ($periodeId !== null) {
            $arguments['periode_id'] = $periodeId;
        }
        return $this->callToolSync('prediksi_skor', $arguments, maxRetries: $maxRetries, retryDelay: $retryDelay);
    }

    public function runPeringatanAgent(int $prodiId, ?int $periodeId = null): array
    {
        $arguments = ['prodi_id' => $prodiId];
        if ($periodeId !== null) {
            $arguments['periode_id'] = $periodeId;
        }
        return $this->callToolSync('peringatan_agent', $arguments);
    }

    public function runGeneratorDokumen(int $prodiId, ?int $periodeId = null, string $jenisDokumen = 'LED'): array
    {
        $arguments = [
            'prodi_id' => $prodiId,
            'jenis_dokumen' => $jenisDokumen,
        ];
        if ($periodeId !== null) {
            $arguments['periode_id'] = $periodeId;
        }
        return $this->callToolSync('generator_dokumen', $arguments);
    }

    public function runIntegrasiSync(string $sumber): array
    {
        return $this->callToolSync('integrasi_sync', ['sumber' => $sumber]);
    }

    public function askRAG(string $question, array $chunks = [], int $topK = 5): array
    {
        return $this->callToolSync('rag_answer', [
            'question' => $question,
            'chunks' => $chunks,
            'top_k' => $topK,
        ], 'rag');
    }

    public function embedText(string $text): array
    {
        return $this->callTool('rag_embed_text', ['text' => $text], 'rag');
    }

    public function searchRAG(string $question, array $chunks, int $topK = 5): array
    {
        return $this->callTool('rag_search', [
            'question' => $question,
            'chunks' => $chunks,
            'top_k' => $topK,
        ], 'rag');
    }

    public function healthCheck(): array
    {
        $result = ['agents' => false, 'rag' => false];

        try {
            $response = Http::timeout(5)->get("{$this->agentsUrl}/health");
            $result['agents'] = $response->successful();
        } catch (Exception $e) {
            Log::debug('MCP agents health check failed', ['error' => $e->getMessage()]);
        }

        try {
            $response = Http::timeout(5)->get("{$this->ragUrl}/health");
            $result['rag'] = $response->successful();
        } catch (Exception $e) {
            Log::debug('MCP rag health check failed', ['error' => $e->getMessage()]);
        }

        return $result;
    }

    // ========================================================================
    // PDDIKTI MCP Methods
    // ========================================================================

    /**
     * Fetch dosen data from PDDIKTI/SISTER API via MCP.
     * Falls back to local DB if MCP unavailable.
     */
    public function fetchPddiktiDosen(array $params = []): array
    {
        try {
            return $this->callToolSync('fetch_dosen', $params);
        } catch (Exception $e) {
            Log::warning("MCP fetch_dosen failed, falling back to local: {$e->getMessage()}");
            return $this->fallbackPddiktiDosen($params);
        }
    }

    /**
     * Fetch prodi data from PDDIKTI/SISTER API via MCP.
     */
    public function fetchPddiktiProdi(array $params = []): array
    {
        try {
            return $this->callToolSync('fetch_prodi', $params);
        } catch (Exception $e) {
            Log::warning("MCP fetch_prodi failed, falling back to local: {$e->getMessage()}");
            return $this->fallbackPddiktiProdi($params);
        }
    }

    /**
     * Get dosen from PDDIKTI (legacy tool).
     */
    public function getPddiktiDosen(string $nidn = '', string $prodiId = ''): array
    {
        $params = [];
        if ($nidn) $params['nidn'] = $nidn;
        if ($prodiId) $params['prodi_id'] = $prodiId;
        return $this->callToolWithFallback('pddikti_get_dosen', $params, []);
    }

    // ========================================================================
    // SINTA MCP Methods
    // ========================================================================

    /**
     * Fetch authors from SINTA API via MCP.
     */
    public function fetchSintaAuthors(array $params = []): array
    {
        try {
            return $this->callToolSync('fetch_authors', $params);
        } catch (Exception $e) {
            Log::warning("MCP fetch_authors failed, falling back: {$e->getMessage()}");
            return $this->fallbackSintaAuthors($params);
        }
    }

    /**
     * Fetch publications from SINTA API via MCP.
     */
    public function fetchSintaPublications(string $authorId, array $params = []): array
    {
        $arguments = array_merge(['author_id' => $authorId], $params);
        try {
            return $this->callToolSync('fetch_publications', $arguments);
        } catch (Exception $e) {
            Log::warning("MCP fetch_publications failed for {$authorId}, falling back: {$e->getMessage()}");
            return $this->fallbackSintaPublications($authorId, $params);
        }
    }

    /**
     * Fetch research data from SINTA API via MCP.
     */
    public function fetchSintaResearches(string $authorId, array $params = []): array
    {
        $arguments = array_merge(['author_id' => $authorId], $params);
        try {
            return $this->callToolSync('fetch_researches', $arguments);
        } catch (Exception $e) {
            Log::warning("MCP fetch_researches failed for {$authorId}, falling back: {$e->getMessage()}");
            return $this->fallbackSintaResearches($authorId, $params);
        }
    }

    /**
     * Fetch community service (PKM) data from SINTA API via MCP.
     */
    public function fetchSintaCommunityServices(string $authorId, array $params = []): array
    {
        $arguments = array_merge(['author_id' => $authorId], $params);
        try {
            return $this->callToolSync('fetch_community_services', $arguments);
        } catch (Exception $e) {
            Log::warning("MCP fetch_community_services failed for {$authorId}, falling back: {$e->getMessage()}");
            return $this->fallbackSintaCommunityServices($authorId, $params);
        }
    }

    /**
     * Search SINTA author by name.
     */
    public function searchSintaAuthor(string $nama, string $afiliasi = ''): array
    {
        $params = ['nama' => $nama];
        if ($afiliasi) $params['afiliasi'] = $afiliasi;
        return $this->callToolWithFallback('sinta_search_author', $params, []);
    }

    // ========================================================================
    // Generic fallback wrapper
    // ========================================================================

    private function callToolWithFallback(string $toolName, array $arguments, mixed $default): mixed
    {
        try {
            return $this->callToolSync($toolName, $arguments);
        } catch (Exception $e) {
            Log::warning("MCP {$toolName} failed: {$e->getMessage()}");
            return $default;
        }
    }

    // ========================================================================
    // Local Fallbacks (when MCP is unavailable)
    // ========================================================================

    private function fallbackPddiktiDosen(array $params): array
    {
        $query = \App\Models\Dosen::where('is_active', true);
        if (!empty($params['nidn'])) {
            $query->where('nidn', $params['nidn']);
        }
        if (!empty($params['prodi_id'])) {
            $query->where('prodi_id', $params['prodi_id']);
        }
        $dosen = $query->limit(200)->get();
        return [
            'status' => 'success',
            'source' => 'local_cache',
            'results' => $dosen->toArray(),
            'total' => $dosen->count(),
        ];
    }

    private function fallbackPddiktiProdi(array $params): array
    {
        $query = \App\Models\Prodi::where('is_active', true);
        if (!empty($params['kode_prodi'])) {
            $query->where('kode_prodi', $params['kode_prodi']);
        }
        if (!empty($params['nama'])) {
            $query->where('nama_prodi', 'ilike', '%' . $params['nama'] . '%');
        }
        $prodi = $query->limit(200)->get();
        return [
            'status' => 'success',
            'source' => 'local_cache',
            'results' => $prodi->toArray(),
            'total' => $prodi->count(),
        ];
    }

    private function fallbackSintaAuthors(array $params): array
    {
        $query = \App\Models\Dosen::whereNotNull('sinta_id')->where('is_active', true);
        if (!empty($params['nama'])) {
            $query->where(function ($q) use ($params) {
                $q->where('nama_depan', 'ilike', '%' . $params['nama'] . '%')
                    ->orWhere('nama_belakang', 'ilike', '%' . $params['nama'] . '%');
            });
        }
        $dosen = $query->limit(200)->get(['id', 'nidn', 'nama_depan', 'nama_belakang', 'sinta_id']);
        return [
            'status' => 'success',
            'source' => 'local_cache',
            'results' => $dosen->toArray(),
            'total' => $dosen->count(),
        ];
    }

    private function fallbackSintaPublications(string $authorId, array $params): array
    {
        $dosen = \App\Models\Dosen::where('sinta_id', $authorId)->first();
        if (!$dosen) {
            return ['status' => 'error', 'message' => 'Dosen not found locally'];
        }
        $publikasi = \App\Models\IntegrasiSintaPublikasi::where('dosen_id', $dosen->id)
            ->limit(200)
            ->get();
        return [
            'status' => 'success',
            'source' => 'local_cache',
            'author_id' => $authorId,
            'publications' => $publikasi->toArray(),
            'total' => $publikasi->count(),
        ];
    }

    private function fallbackSintaResearches(string $authorId, array $params): array
    {
        $dosen = \App\Models\Dosen::where('sinta_id', $authorId)->first();
        if (!$dosen) {
            return ['status' => 'error', 'message' => 'Dosen not found locally'];
        }
        $penelitian = \App\Models\IntegrasiSintaPenelitian::where('dosen_id', $dosen->id)
            ->limit(200)
            ->get();
        return [
            'status' => 'success',
            'source' => 'local_cache',
            'author_id' => $authorId,
            'researches' => $penelitian->toArray(),
            'total' => $penelitian->count(),
        ];
    }

    private function fallbackSintaCommunityServices(string $authorId, array $params): array
    {
        $dosen = \App\Models\Dosen::where('sinta_id', $authorId)->first();
        if (!$dosen) {
            return ['status' => 'error', 'message' => 'Dosen not found locally'];
        }
        $pkm = \App\Models\IntegrasiSintaPkm::where('dosen_id', $dosen->id)
            ->limit(200)
            ->get();
        return [
            'status' => 'success',
            'source' => 'local_cache',
            'author_id' => $authorId,
            'community_services' => $pkm->toArray(),
            'total' => $pkm->count(),
        ];
    }
}
