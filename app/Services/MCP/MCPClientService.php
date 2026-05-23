<?php

declare(strict_types=1);

namespace App\Services\MCP;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MCPClientService
{
    protected string $agentsUrl;

    protected string $ragUrl;

    protected string $apiKey;

    public function __construct()
    {
        $this->agentsUrl = config('mcp.agents_url', 'http://localhost:8001');
        $this->ragUrl = config('mcp.rag_url', 'http://localhost:5001');
        $this->apiKey = config('mcp.api_key');
    }

    /**
     * List all available MCP tools from the agents server
     */
    public function listTools(): array
    {
        try {
            $response = Http::withHeaders([
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
     * Call an MCP tool on the agents server
     */
    public function callTool(string $toolName, array $arguments = [], string $server = 'agents'): array
    {
        $baseUrl = $server === 'rag' ? $this->ragUrl : $this->agentsUrl;

        try {
            $response = Http::withHeaders([
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
            ]);

            throw new Exception("MCP tool call failed: {$response->status()}");
        } catch (Exception $e) {
            Log::error("MCP tool call exception: {$toolName}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Call a tool and wait for result (for task=True tools)
     */
    public function callToolAsync(string $toolName, array $arguments = [], string $server = 'agents', int $maxRetries = 30, int $retryDelay = 1000): array
    {
        $baseUrl = $server === 'rag' ? $this->ragUrl : $this->agentsUrl;

        try {
            // Start the task
            $response = Http::withHeaders([
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

            // If it's a task, poll for completion
            if (isset($result['task_id'])) {
                $taskId = $result['task_id'];

                for ($i = 0; $i < $maxRetries; $i++) {
                    usleep($retryDelay * 1000);

                    $statusResponse = Http::withHeaders([
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
            Log::error("MCP async tool call exception: {$toolName}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Run a warning check via MCP
     */
    public function runPeringatanCheck(int $prodiId): array
    {
        return $this->callToolAsync('peringatan_check', ['prodi_id' => $prodiId]);
    }

    /**
     * Run recommendation generation via MCP
     */
    public function runRekomendasiGenerate(int $prodiId, int $topN = 10): array
    {
        return $this->callToolAsync('rekomendasi_generate', [
            'prodi_id' => $prodiId,
            'top_n' => $topN,
        ]);
    }

    /**
     * Run document verification via MCP
     */
    public function runVerifikasiDokumen(int $prodiId, ?int $docBuktiId = null): array
    {
        $arguments = ['prodi_id' => $prodiId];
        if ($docBuktiId !== null) {
            $arguments['doc_bukti_id'] = $docBuktiId;
        }

        return $this->callToolAsync('verifikasi_dokumen', $arguments);
    }

    /**
     * Run score prediction via MCP
     */
    public function runPrediksiSkor(int $prodiId, ?int $periodeId = null): array
    {
        $arguments = ['prodi_id' => $prodiId];
        if ($periodeId !== null) {
            $arguments['periode_id'] = $periodeId;
        }

        return $this->callToolAsync('prediksi_skor', $arguments);
    }

    /**
     * Run peringatan agent via MCP
     */
    public function runPeringatanAgent(int $prodiId, ?int $periodeId = null): array
    {
        $arguments = ['prodi_id' => $prodiId];
        if ($periodeId !== null) {
            $arguments['periode_id'] = $periodeId;
        }

        return $this->callToolAsync('peringatan_agent', $arguments);
    }

    /**
     * Run document generation via MCP
     */
    public function runGeneratorDokumen(int $prodiId, ?int $periodeId = null, string $jenisDokumen = 'LED'): array
    {
        $arguments = [
            'prodi_id' => $prodiId,
            'jenis_dokumen' => $jenisDokumen,
        ];
        if ($periodeId !== null) {
            $arguments['periode_id'] = $periodeId;
        }

        return $this->callToolAsync('generator_dokumen', $arguments);
    }

    /**
     * Run external data sync via MCP
     */
    public function runIntegrasiSync(string $sumber): array
    {
        return $this->callToolAsync('integrasi_sync', ['sumber' => $sumber]);
    }

    /**
     * Ask RAG a question
     */
    public function askRAG(string $question, array $chunks = [], int $topK = 5): array
    {
        return $this->callToolAsync('rag_answer', [
            'question' => $question,
            'chunks' => $chunks,
            'top_k' => $topK,
        ], 'rag');
    }

    /**
     * Embed text via RAG service
     */
    public function embedText(string $text): array
    {
        return $this->callTool('rag_embed_text', ['text' => $text], 'rag');
    }

    /**
     * Search RAG for relevant sentences
     */
    public function searchRAG(string $question, array $chunks, int $topK = 5): array
    {
        return $this->callTool('rag_search', [
            'question' => $question,
            'chunks' => $chunks,
            'top_k' => $topK,
        ], 'rag');
    }

    /**
     * Check if MCP servers are available
     */
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
