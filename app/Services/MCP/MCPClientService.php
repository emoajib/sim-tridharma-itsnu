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

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("MCP tool call failed: {$toolName}", [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => "{$baseUrl}/mcp/tools/call",
            ]);

            throw new Exception("MCP tool call failed: {$response->status()}");
        } catch (Exception $e) {
            Log::error("MCP tool call exception: {$toolName}", [
                'error' => $e->getMessage(),
                'url' => "{$baseUrl}/mcp/tools/call",
            ]);
            throw $e;
        }
    }

    /**
     * Synchronous MCP call with polling — used by background jobs only.
     * WARNING: Do NOT call from HTTP request handlers; use dispatchTool() instead.
     */
    public function callToolSync(string $toolName, array $arguments = [], string $server = 'agents', int $maxRetries = 20, int $retryDelay = 500): array
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

            if (! $response->successful()) {
                throw new Exception("MCP tool call failed: {$response->status()}");
            }

            $result = $response->json();

            if (isset($result['task_id'])) {
                $taskId = $result['task_id'];

                for ($i = 0; $i < $maxRetries; $i++) {
                    usleep($retryDelay * 1000);

                    $statusResponse = Http::timeout(5)->withHeaders([
                        'X-API-Key' => $this->apiKey,
                        'Accept' => 'application/json',
                    ])->get("{$baseUrl}/mcp/tasks/{$taskId}");

                    if ($statusResponse->successful()) {
                        $status = $statusResponse->json();

                        if ($status['status'] === 'completed') {
                            return $status['result'] ?? [];
                        }

                        if ($status['status'] === 'failed') {
                            throw new Exception('MCP task failed: '.($status['error'] ?? 'Unknown error'));
                        }
                    }
                }

                throw new Exception("MCP task timeout after {$maxRetries} retries");
            }

            return $result;
        } catch (Exception $e) {
            Log::error("MCP sync tool call exception: {$toolName}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Dispatch an MCP tool call as a background job.
     * Returns immediately with a task_id for status polling.
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
        if (! $cached) {
            return ['status' => 'not_found'];
        }

        return $cached;
    }

    public function getTaskResult(string $taskId): array
    {
        $cached = Cache::get("mcp_task_{$taskId}");
        if (! $cached) {
            return ['success' => false, 'error' => 'Task not found'];
        }

        if ($cached['status'] !== 'completed') {
            return ['success' => false, 'error' => 'Task not completed', 'status' => $cached['status']];
        }

        return ['success' => true, 'result' => $cached['result']];
    }

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

    public function runPrediksiSkor(int $prodiId, ?int $periodeId = null): array
    {
        $arguments = ['prodi_id' => $prodiId];
        if ($periodeId !== null) {
            $arguments['periode_id'] = $periodeId;
        }

        return $this->callToolSync('prediksi_skor', $arguments);
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
        $result = [
            'agents' => false,
            'rag' => false,
        ];

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
}
