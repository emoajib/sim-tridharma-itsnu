<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $r) => $r->user());

    // RAG
    Route::post('/rag/ask', [\App\Http\Controllers\Api\KnowledgeBaseController::class, 'ask']);
    Route::get('/rag/status', [\App\Http\Controllers\Api\KnowledgeBaseController::class, 'status']);
});

