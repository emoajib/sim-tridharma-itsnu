<?php

namespace App\Listeners;

use App\Events\AuditSevereFindingCreated;
use App\Models\RiskRegister;
use Illuminate\Support\Facades\Log;

class CreateRiskRegisterFromSevereFinding
{
    const RISK_SCORES = [
        'kritis' => 20,
        'berat' => 15,
        'sedang' => 10,
        'ringan' => 5,
    ];

    /**
     * Auto-create a RiskRegister entry from a severe finding.
     */
    public function handle(AuditSevereFindingCreated $event): void
    {
        $audit = $event->audit;
        $severity = $audit->severity ?? 'ringan';
        $riskScore = self::RISK_SCORES[$severity] ?? 5;

        try {
            RiskRegister::create([
                'prodi_id' => $audit->prodi_id,
                'periode_id' => $audit->periode_id,
                'nama_risiko' => "Temuan Audit: {$audit->judul_audit}",
                'kategori' => 'audit_mutu',
                'dampak' => "Temuan dengan tingkat {$severity}: {$audit->temuan}",
                'probabilitas' => $this->getProbabilityFromSeverity($severity),
                'skor_risiko' => $riskScore,
                'mitigasi' => $audit->rekomendasi ?? 'Belum ada mitigasi',
                'status' => 'open',
                'penanggung_jawab' => $audit->pic_user_id,
            ]);

            Log::info("RiskRegister auto-created from severe finding", [
                'audit_id' => $audit->id,
                'severity' => $severity,
                'risk_score' => $riskScore,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create RiskRegister from severe finding", [
                'audit_id' => $audit->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Map severity to probability level for the risk register.
     */
    private function getProbabilityFromSeverity(string $severity): string
    {
        return match ($severity) {
            'kritis' => 'sangat_tinggi',
            'berat' => 'tinggi',
            'sedang' => 'sedang',
            default => 'rendah',
        };
    }
}
