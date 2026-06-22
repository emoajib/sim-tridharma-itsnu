<?php

namespace App\Models;

use App\Models\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppBrainEntity extends Model
{
    use HasActiveScope, HasFactory, SoftDeletes;

    protected $table = 'app_brain_entities';

    protected $fillable = [
        'ulid', 'type', 'key', 'name',
        'description', 'metadata', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function relations()
    {
        return $this->hasMany(AppBrainRelation::class, 'source_entity_id');
    }

    public function relatedTo()
    {
        return $this->hasMany(AppBrainRelation::class, 'target_entity_id');
    }

    public function snapshots()
    {
        return $this->hasMany(AppBrainSnapshot::class, 'entity_id');
    }
}
