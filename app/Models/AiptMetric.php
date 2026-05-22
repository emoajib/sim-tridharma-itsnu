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
        'periode_id', 'aspek', 'skor', 'target', 'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'skor' => 'float',
            'target' => 'float',
        ];
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class);
    }
}
