<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrxSkorAkreditasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_skor_akreditasi';

    protected $fillable = [
        'prodi_id', 'periode_id', 'skor_total', 'skor_prediksi',
        'confidence_interval', 'probabilitas_unggul',
        'probabilitas_baik_sekali', 'probabilitas_baik',
        'sumber_data', 'is_final',
    ];

    protected function casts(): array
    {
        return [
            'skor_total' => 'float',
            'skor_prediksi' => 'float',
            'confidence_interval' => 'float',
            'probabilitas_unggul' => 'float',
            'probabilitas_baik_sekali' => 'float',
            'probabilitas_baik' => 'float',
            'is_final' => 'boolean',
        ];
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class);
    }
}
