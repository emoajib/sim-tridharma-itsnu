<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReconciliationSuggestion extends Model
{
    use HasFactory;

    protected $table = 'reconciliation_suggestions';

    protected $fillable = [
        'source_type',
        'source_data',
        'target_table',
        'target_id',
        'match_field',
        'match_value',
        'similarity_score',
        'confidence',
        'status',
        'prodi_id',
        'dosen_id',
        'suggested_by',
        'reviewed_by',
        'reviewed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'source_data' => 'array',
            'similarity_score' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
