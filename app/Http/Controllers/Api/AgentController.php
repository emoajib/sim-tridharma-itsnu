<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentRunRequest;
use App\Models\AgentExecutionLog;
use App\Services\MCP\MCPClientService;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    protected MCPClientService $mcp;

    public function __construct(MCPClientService $mcp)
    {
        $this->mcp = $mcp;
    }

    public function run(string $agent, AgentRunRequest $request)
    {
        $agentName = match ($agent) {
            'prediksi' => 'prediksi_skor',
            'peringatan' => 'peringatan_check',
            'verifikasi' => 'verifikasi_dokumen',
            'rekomendasi' => 'rekomendasi_generate',
            'generator' => 'generator_dokumen',
            'integrasi' => 'integrasi_sync',
            default => null,
        };

        if (! $agentName) {
            return response()->json(['error' => 'Invalid agent name'], 400);
        }

        try {
            $result = $this->mcp->callTool($agentName, $request->validated());

            return response()->json([
                'message' => "Agent {$agent} executed",
                'agent' => $agent,
                'status' => 'completed',
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => "Agent {$agent} failed: ".$e->getMessage(),
            ], 500);
        }
    }

    public function status()
    {
        return response()->json([
            'status' => 'operational',
            'service' => 'mcp',
        ]);
    }

    public function latestResults(Request $request)
    {
        return response()->json([]);
    }

    public function logInternal(Request $request)
    {
        $validated = $request->validate([
            'agent_name' => 'required|string',
            'status' => 'required|string|in:success,failed,warning',
            'started_at' => 'required|date',
            'finished_at' => 'required|date|after_or_equal:started_at',
            'duration_ms' => 'nullable|integer|min:0',
            'input_data' => 'nullable|array',
            'output_data' => 'nullable|array',
            'error_message' => 'nullable|string',
            'triggered_by' => 'nullable|string',
        ]);

        $log = AgentExecutionLog::create($validated);

        return response()->json(['success' => true, 'log_id' => $log->id], 201);
    }
}
