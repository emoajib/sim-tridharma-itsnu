<?php

namespace App\Observers;

use App\Events\AgentCompleted;
use App\Models\AgentExecutionLog;

class AgentExecutionLogObserver
{
    public function created(AgentExecutionLog $log): void
    {
        if (in_array($log->status, ['success', 'failed'])) {
            event(new AgentCompleted(
                agent: $log->agent_name,
                status: $log->status,
                data: [
                    'execution_id' => $log->id,
                    'duration_ms' => $log->duration_ms,
                    'triggered_by' => $log->triggered_by,
                ],
                userId: null
            ));
        }
    }
}