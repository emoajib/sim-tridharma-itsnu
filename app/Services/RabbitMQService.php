<?php

/**
 * @deprecated Replaced by MCP protocol via app/Services/MCP/MCPClientService.php
 *             RabbitMQ transport is no longer used. All agents communicate via MCP.
 *             Kept for reference during transition period. Will be removed in next major release.
 */

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQService
{
    protected AMQPStreamConnection $connection;

    protected string $exchange;

    protected string $queue;

    public function __construct()
    {
        Log::warning('RabbitMQService used — DEPRECATED. Use MCPClientService instead.');

        $this->exchange = config('rabbitmq.exchange', 'akreditasi');
        $this->queue = config('rabbitmq.queue', 'agent_tasks');
    }

    protected function connect(): void
    {
        $this->connection = new AMQPStreamConnection(
            config('rabbitmq.host', '127.0.0.1'),
            config('rabbitmq.port', 5672),
            config('rabbitmq.user', 'guest'),
            config('rabbitmq.password', 'guest'),
            config('rabbitmq.vhost', '/')
        );
    }

    public function publish(array $message, string $routingKey = 'agent.task'): void
    {
        $this->connect();
        $channel = $this->connection->channel();
        $channel->exchange_declare($this->exchange, 'topic', false, true, false);
        $channel->queue_declare($this->queue, false, true, false, false);
        $channel->queue_bind($this->queue, $this->exchange, $routingKey);

        $msg = new AMQPMessage(json_encode(array_merge($message, [
            'message_id' => (string) Str::uuid(),
            'timestamp' => now()->toIso8601String(),
        ])), [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'content_type' => 'application/json',
        ]);

        $channel->basic_publish($msg, $this->exchange, $routingKey);
        $channel->close();
        $this->connection->close();
    }

    public function dispatchAgent(string $agent, string $action, array $data = []): void
    {
        $this->publish([
            'agent' => $agent,
            'action' => $action,
            'data' => $data,
        ], "agent.{$agent}");
    }
}
