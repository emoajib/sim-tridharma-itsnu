<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiptMetric extends Model
{
    use HasFactory;

    protected $table = 'aipt_metrics';

    protected $fillable = [
        'periode_id', 'aspek', 'indikator', 'deskripsi', 'target_skor', 'skor_saat_ini', 'status',
    ];

    protected function casts(): array
    {
        return [
            'target_skor' => 'float',
            'skor_saat_ini' => 'float',
        ];
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class);
    }
}
