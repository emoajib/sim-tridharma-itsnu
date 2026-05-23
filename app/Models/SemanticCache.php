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

    protected function casts(): array
    {
        return [
            'sources' => 'array',
            'question_vector' => 'array',
            'last_hit_at' => 'datetime',
        ];
    }
}
