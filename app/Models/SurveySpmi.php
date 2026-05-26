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
        'periode_id', 'spmi_cycle_id', 'responden_type', 'responses',
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

    // NEW: SpmiCycle Relationship
    public function spmiCycle(): BelongsTo
    {
        return $this->belongsTo(SpmiCycle::class, 'spmi_cycle_id');
    }

    // NEW: Helper Methods

    /**
     * Get average NPS score from responses
     */
    public function getNpsScore(): ?float
    {
        $responses = $this->responses;
        if (!isset($responses['nps'])) return null;
        
        return collect($responses['nps'])->avg();
    }

    /**
     * Check if response is complete
     */
    public function isComplete(): bool
    {
        return !empty($this->responses) && $this->diisi_at !== null;
    }
}
