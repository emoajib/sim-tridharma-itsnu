<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppBrainRelation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'app_brain_relations';

    protected $fillable = [
        'source_entity_id', 'target_entity_id',
        'type', 'weight', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'float',
            'metadata' => 'array',
        ];
    }

    public function sourceEntity(): BelongsTo
    {
        return $this->belongsTo(AppBrainEntity::class, 'source_entity_id');
    }

    public function targetEntity(): BelongsTo
    {
        return $this->belongsTo(AppBrainEntity::class, 'target_entity_id');
    }
}
