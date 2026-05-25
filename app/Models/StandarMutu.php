<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StandarMutu extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_standar_mutu';

    protected $fillable = [
        'kategori', 'kode_standar', 'nama_standar', 'deskripsi',
        'sumber', 'referensi_regulasi', 'target_nilai', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'target_nilai' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function auditMutus(): HasMany
    {
        return $this->hasMany(AuditMutu::class, 'standar_mutu_id');
    }

    public function edps(): HasMany
    {
        return $this->hasMany(Edps::class);
    }
}
