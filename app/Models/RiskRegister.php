<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskRegister extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_risk_register';

    protected $fillable = [
        'prodi_id', 'periode_id', 'nama_risiko', 'kategori', 'dampak',
        'probabilitas', 'skor_risiko', 'mitigasi', 'status', 'penanggung_jawab',
    ];

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_id');
    }
}
