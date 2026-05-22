<?php

namespace App\Models;

use App\Models\Traits\HasActiveScope;
use App\Models\Traits\HasCascadeDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prodi extends Model
{
    use HasActiveScope, HasCascadeDeletes, HasFactory, SoftDeletes;

    protected array $cascadeDeletes = ['dosens', 'mahasiswas', 'mataKuliahs', 'kurikulums'];

    protected $table = 'm_prodi';

    protected $fillable = [
        'kode_prodi', 'nama_prodi', 'fakultas_id', 'lembaga_akreditasi_id', 'jenjang',
        'akreditasi', 'sk_akreditasi', 'tanggal_kadaluarsa', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'tanggal_kadaluarsa' => 'date',
        ];
    }

    public function fakultas(): BelongsTo
    {
        return $this->belongsTo(Fakultas::class);
    }

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(LembagaAkreditasi::class, 'lembaga_akreditasi_id');
    }

    public function dosens(): HasMany
    {
        return $this->hasMany(Dosen::class, 'prodi_id');
    }

    public function mahasiswas(): HasMany
    {
        return $this->hasMany(Mahasiswa::class, 'prodi_id');
    }

    public function mataKuliahs(): HasMany
    {
        return $this->hasMany(MataKuliah::class, 'prodi_id');
    }

    public function kurikulums(): HasMany
    {
        return $this->hasMany(Kurikulum::class, 'prodi_id');
    }
}
