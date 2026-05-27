<?php

use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\CascadingIkuController;
use App\Http\Controllers\Api\IkuController;
use App\Http\Controllers\Api\KnowledgeBaseController;
use App\Http\Controllers\Api\RkatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/user', fn (Request $r) => $r->user());

    // Agent AI — throttled to prevent resource exhaustion
    Route::post('/agents/{agent}/run', [AgentController::class, 'run'])->name('agents.run')
        ->middleware('throttle:10,1');
    Route::get('/agents/status', [AgentController::class, 'status'])->name('agents.status');
    Route::get('/agents/latest', [AgentController::class, 'latestResults'])->name('agents.latest');

    // M13: RKAT & IKU
    Route::prefix('rkat')->group(function () {
        Route::get('/proposals', [RkatController::class, 'index']);
        Route::post('/proposals', [RkatController::class, 'store']);
        Route::post('/proposals/{id}/approve', [RkatController::class, 'approve']);
        Route::get('/pagu-check', [RkatController::class, 'checkPagu']);
    });

    // IKU for agents
    Route::prefix('iku')->group(function () {
        Route::get('/', [IkuController::class, 'index']);
        Route::get('/cascading', [CascadingIkuController::class, 'index']);
    });
});

// Internal API for AI Microservice (FastAPI)
Route::post('/internal/agents/log', [AgentController::class, 'logInternal'])
    ->middleware(['auth:sanctum', 'throttle:60,1']);
