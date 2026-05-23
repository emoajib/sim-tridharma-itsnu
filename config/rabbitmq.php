<?php

/**
 * @deprecated Replaced by config/mcp.php and app/Services/MCP/MCPClientService.php
 *             RabbitMQ transport is no longer used. All agents communicate via MCP.
 *             Kept for reference during transition period. Will be removed in next major release.
 */

return [
    'host' => env('RABBITMQ_HOST', '127.0.0.1'),
    'port' => env('RABBITMQ_PORT', 5672),
    'user' => env('RABBITMQ_USER', 'guest'),
    'password' => env('RABBITMQ_PASSWORD', 'guest'),
    'vhost' => env('RABBITMQ_VHOST', '/'),
    'exchange' => env('RABBITMQ_EXCHANGE', 'akreditasi'),
    'queue' => env('RABBITMQ_QUEUE', 'agent_tasks'),
];
