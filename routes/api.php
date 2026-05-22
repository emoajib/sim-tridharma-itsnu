<?php

use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\KnowledgeBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $r) => $r->user());

    // RAG
    Route::post('/rag/ask', [KnowledgeBaseController::class, 'ask']);
    Route::get('/rag/status', [KnowledgeBaseController::class, 'status']);

    // Agent AI
    Route::post('/agents/{agent}/run', [AgentController::class, 'run']);
    Route::get('/agents/status', [AgentController::class, 'status']);
    Route::get('/agents/latest', [AgentController::class, 'latestResults']);
});

// Internal API for AI Microservice (FastAPI)
Route::post('/internal/agents/log', [AgentController::class, 'logInternal'])
    ->middleware(['auth:sanctum', 'throttle:60,1']);
