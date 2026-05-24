<?php

namespace App\Models;

use App\Models\Traits\HasActiveScope;
use App\Models\Traits\HasCascadeDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dosen extends Model
{
    use HasActiveScope, HasCascadeDeletes, HasFactory, SoftDeletes;

    protected array $cascadeDeletes = ['pendidikan', 'penelitian', 'publikasi', 'pkm', 'bkd'];

    protected $table = 'm_dosen';

    protected $fillable = [
        'nidn', 'nip', 'nama_depan', 'nama_belakang', 'gelar_depan', 'gelar_belakang',
        'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'prodi_id',
        'pendidikan_terakhir', 'jabatan_fungsional', 'status_aktivitas',
        'email', 'telepon', 'is_active', 'sinta_id',
    ];

    protected $guarded = [
        'sinta_score_overall', 'sinta_score_3yr', 'status_verifikasi_sinta',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'tanggal_lahir' => 'date',
        ];
    }

    protected $appends = ['nama'];

    public function getNamaAttribute(): string
    {
        return trim("{$this->nama_depan} {$this->nama_belakang}");
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function pendidikan(): HasMany
    {
        return $this->hasMany(KegiatanPendidikan::class, 'dosen_id');
    }

    public function penelitian(): HasMany
    {
        return $this->hasMany(Penelitian::class, 'dosen_id');
    }

    public function publikasi(): HasMany
    {
        return $this->hasMany(Publikasi::class, 'dosen_id');
    }

    public function pkm(): HasMany
    {
        return $this->hasMany(Pkm::class, 'dosen_id');
    }

    public function bkd(): HasMany
    {
        return $this->hasMany(Bkd::class, 'dosen_id');
    }

    /**
     * TEMPORARY relationship added for A1 analysis (multiple user links).
     * This is READ-ONLY and for investigation purposes only.
     * Can be removed later once analysis is complete.
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\User::class, 'dosen_id');
    }
}
