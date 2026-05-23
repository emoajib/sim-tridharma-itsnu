<?php

/**
 * @deprecated Replaced by direct MCP tool calls via MCPClientService.
 *             dispatch() kept for backward compatibility but routes to MCP.
 *             Will be removed in next major release.
 */

namespace App\Services\Agent;

use App\Models\AgentExecutionLog;
use App\Models\AgentGeneratorHistory;
use App\Models\AgentPeringatanLog;
use App\Models\AgentPredictionHistory;
use App\Services\MCP\MCPClientService;

class AgentOrchestrationService
{
    protected array $allowedAgents = [
        'verifikasi', 'prediksi', 'rekomendasi', 'peringatan', 'generator', 'integrasi',
    ];

    protected MCPClientService $mcp;

    public function __construct(MCPClientService $mcp)
    {
        $this->mcp = $mcp;
    }

    public function getAllowedAgents(): array
    {
        return $this->allowedAgents;
    }

    public function isValidAgent(string $agent): bool
    {
        return in_array($agent, $this->allowedAgents, true);
    }

    public function dispatch(string $agent, array $data): void
    {
        $mcpTool = match ($agent) {
            'peringatan' => 'peringatan_check',
            'prediksi' => 'prediksi_skor',
            'verifikasi' => 'verifikasi_dokumen',
            'rekomendasi' => 'rekomendasi_generate',
            'generator' => 'generator_dokumen',
            'integrasi' => 'integrasi_sync',
            default => null,
        };

        if ($mcpTool) {
            $this->mcp->callTool($mcpTool, $data);
        }
    }

    public function logExecution(array $data): AgentExecutionLog
    {
        return AgentExecutionLog::create($data);
    }

    public function getStatus(): array
    {
        $recent = AgentExecutionLog::latest()->take(20)->get();

        $agents = [];
        foreach ($this->allowedAgents as $name) {
            $lastRun = AgentExecutionLog::where('agent_name', $name)
                ->latest()
                ->first();

            $agents[$name] = [
                'status' => $lastRun ? $lastRun->status : 'idle',
                'last_run' => $lastRun?->finished_at,
            ];
        }

        return [
            'agents' => $agents,
            'recent_logs' => $recent,
        ];
    }

    public function getLatestResults(?string $after = null): array
    {
        $logs = AgentExecutionLog::where('status', 'success')
            ->when($after, fn ($q) => $q->where('created_at', '>', $after))
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return [
            'logs' => $logs,
            'predictions' => AgentPredictionHistory::orderByDesc('created_at')->limit(5)->get(),
            'warnings' => AgentPeringatanLog::where('is_read', false)->orderByDesc('created_at')->limit(5)->get(),
            'generations' => AgentGeneratorHistory::orderByDesc('created_at')->limit(5)->get(),
        ];
    }
}
