<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MCP Server URLs
    |--------------------------------------------------------------------------
    */
    'agents_url' => env('MCP_AGENTS_URL', 'http://localhost:8001'),
    'rag_url' => env('MCP_RAG_URL', 'http://localhost:5001'),

    /*
    |--------------------------------------------------------------------------
    | MCP API Key (for migration period - will be replaced by OAuth 2.1)
    |--------------------------------------------------------------------------
    */
    'api_key' => env('MCP_API_KEY', env('AGENT_API_KEY')),

    /*
    |--------------------------------------------------------------------------
    | MCP Settings
    |--------------------------------------------------------------------------
    */
    'timeout' => env('MCP_TIMEOUT', 60),
    'retry_delay' => env('MCP_RETRY_DELAY', 1000), // milliseconds
    'max_retries' => env('MCP_MAX_RETRIES', 30),
];
