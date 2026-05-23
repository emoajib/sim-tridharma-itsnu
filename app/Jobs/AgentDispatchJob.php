<?php

/**
 * @deprecated Replaced by direct MCP tool calls via MCPClientService.
 *             Kept for reference during transition. Will be removed in next major release.
 */

namespace App\Jobs;

use App\Services\MCP\MCPClientService;
use App\Services\RabbitMQService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AgentDispatchJob implements ShouldQueue
{
    use Queueable;

    public string $agent;

    public string $action;

    public array $data;

    public function __construct(string $agent, string $action, array $data = [])
    {
        $this->agent = $agent;
        $this->action = $action;
        $this->data = $data;
    }

    public function handle(RabbitMQService $rabbitMQ, MCPClientService $mcp): void
    {
        Log::warning('AgentDispatchJob used — DEPRECATED. Call MCP tools directly.');

        try {
            $mcpTool = match ($this->agent) {
                'peringatan' => 'peringatan_check',
                'prediksi' => 'prediksi_skor',
                'verifikasi' => 'verifikasi_dokumen',
                'rekomendasi' => 'rekomendasi_generate',
                'generator' => 'generator_dokumen',
                'integrasi' => 'integrasi_sync',
                default => null,
            };

            if ($mcpTool) {
                $mcp->callTool($mcpTool, $this->data);
                Log::info("Agent dispatched via MCP: {$this->agent}.{$this->action}");
                return;
            }

            $rabbitMQ->dispatchAgent($this->agent, $this->action, $this->data);
            Log::info("Agent dispatched via RabbitMQ (legacy): {$this->agent}.{$this->action}", $this->data);
        } catch (\Exception $e) {
            Log::error("Agent dispatch failed: {$this->agent}.{$this->action} - {$e->getMessage()}");
            throw $e;
        }
    }
}
