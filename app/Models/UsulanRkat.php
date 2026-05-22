<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsulanRkat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_usulan_rkat';

    protected $fillable = [
        'prodi_id',
        'periode_id',
        'judul_kegiatan',
        'deskripsi_kegiatan',
        'estimasi_biaya',
        'iku_id',
        'indikator_akreditasi_id',
        'status',
        'komentar_reviewer',
        'user_id',
    ];

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_id');
    }

    public function iku(): BelongsTo
    {
        return $this->belongsTo(IndikatorIku::class, 'iku_id');
    }

    public function indikatorAkreditasi(): BelongsTo
    {
        return $this->belongsTo(IndikatorAkreditasi::class, 'indikator_akreditasi_id');
    }

    public function pengusul(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(RkatApprovalLog::class, 'rkat_id');
    }
}
