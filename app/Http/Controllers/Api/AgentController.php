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

    public function latestResults(Request $request)
    {
        $after = $request->get('after');
        
        $logs = \App\Models\AgentExecutionLog::where('status', 'success')
            ->when($after, fn($q) => $q->where('created_at', '>', $after))
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $predictions = \App\Models\AgentPredictionHistory::orderByDesc('created_at')->limit(5)->get();
        $warnings = \App\Models\AgentPeringatanLog::where('is_read', false)->orderByDesc('created_at')->limit(5)->get();
        $generations = \App\Models\AgentGeneratorHistory::orderByDesc('created_at')->limit(5)->get();

        return response()->json([
            'logs' => $logs,
            'predictions' => $predictions,
            'warnings' => $warnings,
            'generations' => $generations,
        ]);
    }
}
