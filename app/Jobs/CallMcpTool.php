<?php

namespace App\Jobs;

use App\Services\MCP\MCPClientService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CallMcpTool implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 360;
    public int $maxRetries = 50;
    public int $retryDelay = 500;
    public int $tries = 1;

    protected string $taskId;

    public function __construct(
        protected string $toolName,
        protected array $arguments = [],
        protected string $server = 'agents',
    ) {
        $this->taskId = Str::uuid()->toString();
    }

    public function handle(MCPClientService $mcp): void
    {
        $cacheKey = "mcp_task_{$this->taskId}";
        Cache::put($cacheKey, ['status' => 'processing'], 600);

        try {
            $result = $mcp->callToolSync($this->toolName, $this->arguments, $this->server);

            Cache::put($cacheKey, [
                'status' => 'completed',
                'result' => $result,
            ], 600);

            Log::info("MCP task completed: {$this->toolName}", [
                'task_id' => $this->taskId,
                'server' => $this->server,
            ]);
        } catch (\Throwable $e) {
            Cache::put($cacheKey, [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ], 600);

            Log::error("MCP task failed: {$this->toolName}", [
                'task_id' => $this->taskId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function getTaskId(): string
    {
        return $this->taskId;
    }
}
