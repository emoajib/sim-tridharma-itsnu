<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Edps extends Model
{
    use HasFactory;

    protected $table = 'trx_edps';

    protected $fillable = [
        'prodi_id', 'periode_id', 'standar_mutu_id',
        'target', 'capaian', 'analisis', 'bukti_file', 'status',
    ];

    protected function casts(): array
    {
        return [
            'target' => 'decimal:2',
            'capaian' => 'decimal:2',
        ];
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_id');
    }

    public function standarMutu(): BelongsTo
    {
        return $this->belongsTo(StandarMutu::class);
    }
}
