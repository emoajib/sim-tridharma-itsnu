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
        'prodi_id', 'periode_id', 'spmi_cycle_id', 'standar_mutu_id',
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

    // NEW: SpmiCycle Relationship
    public function spmiCycle(): BelongsTo
    {
        return $this->belongsTo(SpmiCycle::class, 'spmi_cycle_id');
    }

    // NEW: Helper Methods

    /**
     * Calculate achievement percentage
     */
    public function getAchievementPercentage(): float
    {
        if (!$this->target || $this->target == 0) return 0;
        return round(($this->capaian / $this->target) * 100, 2);
    }

    /**
     * Check if target is achieved (80% threshold)
     */
    public function isTargetAchieved(): bool
    {
        return $this->getAchievementPercentage() >= 80;
    }

    /**
     * Get performance indicator
     */
    public function getPerformanceIndicator(): string
    {
        $percentage = $this->getAchievementPercentage();
        if ($percentage >= 100) return 'excellent';
        if ($percentage >= 80) return 'good';
        if ($percentage >= 60) return 'satisfactory';
        return 'needs_improvement';
    }
}
