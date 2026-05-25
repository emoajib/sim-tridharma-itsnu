<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class IndikatorIku extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_indikator_iku';

    protected $fillable = [
        'kode_iku',
        'nama_indikator',
        'deskripsi',
        'satuan',
        'bobot',
        'lembaga_id',
        'target',
        'is_active',
    ];

    protected $casts = [
        'target' => 'decimal:2',
        'bobot' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(LembagaAkreditasi::class, 'lembaga_id');
    }

    public function cascadingTarget(): HasMany
    {
        return $this->hasMany(CascadingIku::class, 'iku_id');
    }

    public function usulanRkat(): HasMany
    {
        return $this->hasMany(UsulanRkat::class, 'iku_id');
    }
}
