<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Service Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi Python AI Service untuk embedding dan RAG answer.
    |
    */

    'base_url' => env('AI_SERVICE_URL', 'http://127.0.0.1:5001'),
    'internal_key' => env('AI_INTERNAL_KEY', 'default-internal-key'),

    // Google Gemini Configuration
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('BRAIN_GEMINI_MODEL', 'gemini-1.5-flash'),
    ],

    // OpenAI Configuration (Failover)
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    ],
];
