<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReconciliationSuggestion extends Model
{
    use HasFactory;

    protected $table = 'reconciliation_suggestions';

    protected $fillable = [
        'source_type',
        'source_id',
        'target_type',
        'target_id',
        'field',
        'source_value',
        'target_value',
        'similarity_score',
        'status',
        'reviewed_by',
        'reviewed_at',
        'notes',
    ];

    protected $casts = [
        'similarity_score' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];
}
