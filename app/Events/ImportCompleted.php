<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public string $type,
        public int $successRows,
        public int $failedRows,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("imports.{$this->userId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'import.completed';
    }
}
