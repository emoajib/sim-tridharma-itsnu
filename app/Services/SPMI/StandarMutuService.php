<?php

namespace App\Services\SPMI;

use App\Models\AuditMutu;
use App\Models\Edps;
use App\Models\Prodi;
use App\Models\StandarMutu;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StandarMutuService
{
    /**
     * Update a standar mutu and optionally sync target changes to EDPS.
     */
    public function update(StandarMutu $standar, array $data): void
    {
        $oldTarget = $standar->target_nilai;

        DB::transaction(function () use ($standar, $data, $oldTarget) {
            $standar->update($data);

            // If target_nilai changed, sync to all active prodi EDPS
            if (isset($data['target_nilai']) && (float) $data['target_nilai'] !== (float) $oldTarget) {
                $this->syncTargetToEdps($standar);
            }

            Log::info("Standar Mutu updated", [
                'standar_id' => $standar->id,
                'kode_standar' => $standar->kode_standar,
                'changes' => array_keys($data),
            ]);
        });
    }

    /**
     * Deactivate a standar and archive related open temuan.
     */
    public function deactivate(StandarMutu $standar): void
    {
        DB::transaction(function () use ($standar) {
            $standar->update(['is_active' => false]);

            // Archive related open temuan (non-closed/non-archived)
            $affectedCount = AuditMutu::where('standar_mutu_id', $standar->id)
                ->whereNotIn('status', ['closed', 'archived'])
                ->update([
                    'status' => 'archived',
                    'is_locked' => true,
                    'locked_at' => now(),
                ]);

            Log::info("Standar Mutu deactivated", [
                'standar_id' => $standar->id,
                'kode_standar' => $standar->kode_standar,
                'archived_temuan' => $affectedCount,
            ]);
        });
    }

    /**
     * Sync the new target_nilai to EDPS entries for all active prodi.
     */
    private function syncTargetToEdps(StandarMutu $standar): void
    {
        $activeProdis = Prodi::where('is_active', true)->get(['id']);

        foreach ($activeProdis as $prodi) {
            // Update or create EDPS entry for this standar
            Edps::updateOrCreate(
                [
                    'prodi_id' => $prodi->id,
                    'standar_mutu_id' => $standar->id,
                ],
                [
                    'target' => $standar->target_nilai,
                    'status' => 'draft',
                ]
            );
        }

        Log::info("EDPS target synced for standar", [
            'standar_id' => $standar->id,
            'target_nilai' => $standar->target_nilai,
            'prodi_count' => $activeProdis->count(),
        ]);
    }
}
