<?php

use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\KnowledgeBaseController;
use App\Http\Controllers\Api\RkatController;
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

    // M13: RKAT & IKU
    Route::prefix('rkat')->group(function () {
        Route::get('/proposals', [RkatController::class, 'index']);
        Route::post('/proposals', [RkatController::class, 'store']);
        Route::post('/proposals/{id}/approve', [RkatController::class, 'approve']);
        Route::get('/pagu-check', [RkatController::class, 'checkPagu']);
    });
});

// Internal API for AI Microservice (FastAPI)
Route::post('/internal/agents/log', [AgentController::class, 'logInternal'])
    ->middleware(['auth:sanctum', 'throttle:60,1']);
