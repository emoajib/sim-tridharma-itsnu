<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property array $embedding */
class KnowledgeBaseChunk extends Model
{
    use HasFactory;

    protected $fillable = ['document_id', 'chunk_index', 'content', 'embedding'];

    // NOTE: 'embedding' column is vector(384) from pgvector, NOT json/array.
    // Eloquent cannot cast vector type, so no cast is defined here.
    protected function casts(): array
    {
        return [];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseDocument::class, 'document_id');
    }
}
