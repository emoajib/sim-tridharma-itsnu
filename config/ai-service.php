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
];
