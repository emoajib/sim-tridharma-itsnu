<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $r) => $r->user());

    // RAG
    Route::post('/rag/ask', [\App\Http\Controllers\Api\KnowledgeBaseController::class, 'ask']);
    Route::get('/rag/status', [\App\Http\Controllers\Api\KnowledgeBaseController::class, 'status']);

    // Agent AI
    Route::post('/agents/{agent}/run', [\App\Http\Controllers\Api\AgentController::class, 'run']);
    Route::get('/agents/status', [\App\Http\Controllers\Api\AgentController::class, 'status']);
    Route::get('/agents/latest', [\App\Http\Controllers\Api\AgentController::class, 'latestResults']);
});

// Internal API for AI Microservice (FastAPI)
Route::post('/internal/agents/log', [\App\Http\Controllers\Api\AgentController::class, 'logInternal'])
    ->middleware(['auth:sanctum', 'throttle:60,1']);
