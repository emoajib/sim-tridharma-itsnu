<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Vetted by AI - Manual Review Required by Senior Engineer/Manager
 */
class SemanticCache extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'sources',
        'question_vector',
        'provider',
        'hit_count',
        'last_hit_at',
    ];

    // NOTE: 'question_vector' column is vector(384) from pgvector, NOT json/array.
    // Eloquent cannot cast vector type, so no cast is defined for it here.
    protected function casts(): array
    {
        return [
            'sources' => 'array',
            'last_hit_at' => 'datetime',
        ];
    }
}
