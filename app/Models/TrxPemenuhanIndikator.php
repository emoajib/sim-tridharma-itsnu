<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrxPemenuhanIndikator extends Model
{
    use HasFactory;

    protected $table = 'trx_pemenuhan_indikator';

    protected $fillable = [
        'prodi_id', 'periode_id', 'indikator_id', 'capaian', 'nilai', 'status', 'catatan',
    ];

    protected $guarded = ['is_verified'];

    protected function casts(): array
    {
        return [
            'nilai' => 'float',
            'is_verified' => 'boolean',
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

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(IndikatorAkreditasi::class, 'indikator_id');
    }
}
