<?php

namespace App\Jobs;

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

    public function handle(RabbitMQService $rabbitMQ): void
    {
        try {
            $rabbitMQ->dispatchAgent($this->agent, $this->action, $this->data);

            Log::info("Agent dispatched: {$this->agent}.{$this->action}", $this->data);
        } catch (\Exception $e) {
            Log::error("Agent dispatch failed: {$this->agent}.{$this->action} - {$e->getMessage()}");
            throw $e;
        }
    }
}
