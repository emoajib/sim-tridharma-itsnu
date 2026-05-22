<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Services\Rkat;

use App\Models\RkatApprovalLog;
use App\Models\RkatPagu;
use App\Models\UsulanRkat;
use Exception;
use Illuminate\Support\Facades\DB;

class RkatService
{
    /**
     * Submit a new budget proposal
     */
    public function submitUsulan(array $data, int $userId): UsulanRkat
    {
        return UsulanRkat::create(array_merge($data, [
            'user_id' => $userId,
            'status' => 'submitted',
        ]));
    }

    /**
     * Process approval/rejection of a proposal
     */
    public function processApproval(int $rkatId, string $action, int $reviewerId, ?string $keterangan = null): UsulanRkat
    {
        return DB::transaction(function () use ($rkatId, $action, $reviewerId, $keterangan) {
            $usulan = UsulanRkat::findOrFail($rkatId);

            if ($action === 'approve') {
                // Check Pagu Availability
                $pagu = $this->checkPaguAvailability(
                    $usulan->prodi_id,
                    'Prodi',
                    $usulan->periode_id,
                    $usulan->estimasi_biaya
                );

                if (! $pagu['available']) {
                    throw new Exception("Plafon anggaran tidak mencukupi. Sisa pagu: Rp " . number_format($pagu['remaining'], 0, ',', '.'));
                }

                // Update terpakai in Pagu
                RkatPagu::where([
                    'unit_type' => 'Prodi',
                    'unit_id' => $usulan->prodi_id,
                    'periode_id' => $usulan->periode_id,
                ])->increment('terpakai', $usulan->estimasi_biaya);

                $usulan->status = 'approved';
            } elseif ($action === 'reject') {
                $usulan->status = 'rejected';
            } elseif ($action === 'revise') {
                $usulan->status = 'revised';
            }

            $usulan->komentar_reviewer = $keterangan;
            $usulan->save();

            // Log action
            RkatApprovalLog::create([
                'rkat_id' => $usulan->id,
                'user_id' => $reviewerId,
                'action' => $action,
                'keterangan' => $keterangan,
            ]);

            return $usulan;
        });
    }

    /**
     * Check if budget is within ceiling limits
     */
    public function checkPaguAvailability(int $unitId, string $unitType, int $periodeId, float $amount): array
    {
        $pagu = RkatPagu::where([
            'unit_type' => $unitType,
            'unit_id' => $unitId,
            'periode_id' => $periodeId,
        ])->first();

        if (! $pagu) {
            return [
                'available' => false,
                'remaining' => 0,
                'total' => 0,
            ];
        }

        $remaining = $pagu->pagu_total - $pagu->terpakai;

        return [
            'available' => $remaining >= $amount,
            'remaining' => $remaining,
            'total' => $pagu->pagu_total,
        ];
    }

    /**
     * Get list of proposals for a unit
     */
    public function getProposalsByUnit(int $prodiId, ?int $periodeId = null)
    {
        $query = UsulanRkat::where('prodi_id', $prodiId);
        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }
        return $query->with(['iku', 'indikatorAkreditasi', 'pengusul'])->orderBy('created_at', 'desc')->get();
    }
}
