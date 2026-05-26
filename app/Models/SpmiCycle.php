<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpmiCycle extends Model
{
    use HasFactory;

    protected $table = 'spmi_cycles';

    protected $fillable = [
        'prodi_id', 'periode_id', 'instrumen_id', 'tahap', 'kategori', 'nama_siklus', 'tanggal_mulai', 'tanggal_selesai',
        'persentase_selesai', 'status', 'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'persentase_selesai' => 'float',
        ];
    }

    // === Existing Relationships ===
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_id');
    }

    public function instrumen(): BelongsTo
    {
        return $this->belongsTo(InstrumenAkreditasi::class, 'instrumen_id');
    }

    // === NEW: PPEPP Workflow Relationships ===

    /**
     * Audit Mutu entries for this cycle
     */
    public function auditMutus(): HasMany
    {
        return $this->hasMany(AuditMutu::class, 'spmi_cycle_id');
    }

    /**
     * CAPA entries linked to this cycle (via AuditMutu or directly)
     */
    public function capas(): HasMany
    {
        return $this->hasMany(Capa::class, 'spmi_cycle_id');
    }

    /**
     * EDPs entries for this cycle
     */
    public function edps(): HasMany
    {
        return $this->hasMany(Edps::class, 'spmi_cycle_id');
    }

    /**
     * Survey SPMI entries for this cycle
     */
    public function surveySpmis(): HasMany
    {
        return $this->hasMany(SurveySpmi::class, 'spmi_cycle_id');
    }

    /**
     * RTM action items linked to this cycle
     */
    public function rtmActionItems(): HasMany
    {
        return $this->hasMany(RtmActionItem::class, 'spmi_cycle_id');
    }

    /**
     * SPMI documents linked to this cycle
     */
    public function spmiDokumens(): HasMany
    {
        return $this->hasMany(SpmiDokumen::class, 'spmi_cycle_id');
    }

    // === Helper Methods ===

    /**
     * Get overall progress percentage based on related entities
     */
    public function getOverallProgress(): float
    {
        $totalTasks = $this->auditMutus()->count()
                      + $this->capas()->count()
                      + $this->edps()->count();

        if ($totalTasks === 0) return 0;

        $completedTasks = $this->auditMutus()->where('status', 'closed')->count()
                          + $this->capas()->where('status', 'closed')->count()
                          + $this->edps()->where('status', 'reviewed')->count();

        return round(($completedTasks / $totalTasks) * 100, 2);
    }

    /**
     * Check if cycle is in PPEPP phase
     */
    public function isInPhase(string $phase): bool
    {
        return $this->tahap === $phase;
    }

    /**
     * Get all action items that need attention
     */
    public function getPendingActions(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->capas()
            ->where('status', 'open')
            ->orWhere('status', 'in_progress')
            ->get()
            ->merge(
                $this->rtmActionItems()->where('status', 'open')->get()
            );
    }
}
