<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentRunRequest;
use App\Services\Agent\AgentOrchestrationService;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function __construct(
        protected AgentOrchestrationService $orchestrator,
    ) {}

    public function run(string $agent, AgentRunRequest $request)
    {
        if (! $this->orchestrator->isValidAgent($agent)) {
            return response()->json(['error' => 'Invalid agent name'], 400);
        }

        $this->orchestrator->dispatch($agent, $request->validated());

        return response()->json([
            'message' => "Agent {$agent} dispatched",
            'agent' => $agent,
            'status' => 'queued',
        ]);
    }

    public function logInternal(Request $request)
    {
        $validated = $request->validate([
            'agent_name' => 'required|string',
            'status' => 'required|string',
            'started_at' => 'required|date',
            'finished_at' => 'required|date',
            'duration_ms' => 'required|integer',
            'input_data' => 'nullable|array',
            'output_data' => 'nullable|array',
            'error_message' => 'nullable|string',
            'triggered_by' => 'nullable|string',
        ]);

        $log = $this->orchestrator->logExecution($validated);

        return response()->json([
            'success' => true,
            'log_id' => $log->id,
        ]);
    }

    public function status()
    {
        return response()->json($this->orchestrator->getStatus());
    }

    public function latestResults(Request $request)
    {
        return response()->json($this->orchestrator->getLatestResults($request->get('after')));
    }
}
