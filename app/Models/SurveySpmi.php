<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveySpmi extends Model
{
    use HasFactory;

    protected $table = 'trx_survey_spmi';

    protected $fillable = [
        'periode_id', 'responden_type', 'responses',
        'skor_rata_rata', 'token', 'diisi_at',
    ];

    protected function casts(): array
    {
        return [
            'responses' => 'json',
            'skor_rata_rata' => 'decimal:2',
            'diisi_at' => 'datetime',
        ];
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_id');
    }
}
