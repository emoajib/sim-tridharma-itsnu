<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppBrainSnapshot extends Model
{
    protected $table = 'app_brain_snapshots';

    public $timestamps = false;

    protected $fillable = [
        'entity_id', 'version', 'payload',
        'hash', 'created_by', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(AppBrainEntity::class, 'entity_id');
    }
}
