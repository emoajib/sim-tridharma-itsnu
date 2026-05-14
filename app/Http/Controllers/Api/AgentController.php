<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\AgentDispatchJob;
use App\Models\AgentExecutionLog;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function run(string $agent)
    {
        $allowed = ['verifikasi', 'prediksi', 'rekomendasi', 'peringatan', 'generator', 'integrasi'];

        if (!in_array($agent, $allowed)) {
            return response()->json(['error' => 'Invalid agent name'], 400);
        }

        AgentDispatchJob::dispatch($agent, 'run', request()->all());

        return response()->json([
            'message' => "Agent {$agent} dispatched",
            'agent' => $agent,
            'status' => 'queued',
        ]);
    }

    public function status()
    {
        $recent = \App\Models\AgentExecutionLog::latest()->take(20)->get();

        return response()->json([
            'agents' => [
                'verifikasi' => ['status' => 'active', 'last_run' => null],
                'prediksi' => ['status' => 'active', 'last_run' => null],
                'rekomendasi' => ['status' => 'active', 'last_run' => null],
                'peringatan' => ['status' => 'active', 'last_run' => null],
                'generator' => ['status' => 'active', 'last_run' => null],
                'integrasi' => ['status' => 'active', 'last_run' => null],
            ],
            'recent_logs' => $recent,
        ]);
    }
}
