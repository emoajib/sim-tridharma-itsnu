<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgentCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $agent;
    public string $status;
    public array $data;
    public ?int $userId;

    public function __construct(string $agent, string $status, array $data, ?int $userId = null)
    {
        $this->agent = $agent;
        $this->status = $status;
        $this->data = $data;
        $this->userId = $userId;
    }

    public function broadcastOn(): array
    {
        $channels = [];
        
        if ($this->userId) {
            $channels[] = new PrivateChannel('user.' . $this->userId);
        }
        
        $channels[] = new Channel('agent-results');
        
        return $channels;
    }

    public function broadcastWith(): array
    {
        return [
            'agent' => $this->agent,
            'status' => $this->status,
            'data' => $this->data,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}