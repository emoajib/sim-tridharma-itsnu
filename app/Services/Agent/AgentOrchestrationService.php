<?php

namespace App\Services\Agent;

use App\Jobs\AgentDispatchJob;
use App\Models\AgentExecutionLog;
use App\Models\AgentGeneratorHistory;
use App\Models\AgentPeringatanLog;
use App\Models\AgentPredictionHistory;

class AgentOrchestrationService
{
    protected array $allowedAgents = [
        'verifikasi', 'prediksi', 'rekomendasi', 'peringatan', 'generator', 'integrasi',
    ];

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
        AgentDispatchJob::dispatch($agent, 'run', $data);
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
