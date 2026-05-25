<?php

namespace App\Jobs;

use App\Events\CapaDeadlineApproaching;
use App\Models\AgentPeringatanLog;
use App\Models\User;
use App\Services\SPMI\CapaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckCapaDeadline implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job to check for overdue and approaching CAPA deadlines.
     */
    public function handle(CapaService $capaService): void
    {
        Log::info("CheckCapaDeadline job started");

        try {
            // --- 1. Process overdue CAPAs ---
            $overdue = $capaService->getOverdue();
            $overdueCount = 0;

            foreach ($overdue as $capa) {
                try {
                    AgentPeringatanLog::create([
                        'prodi_id' => $capa->auditMutu?->prodi_id,
                        'dosen_id' => $capa->picUser?->dosen_id,
                        'jenis_peringatan' => 'capa_deadline',
                        'tingkat' => 'critical',
                        'pesan' => "CAPA #{$capa->id} telah melewati deadline! Deadline: {$capa->corrective_deadline->format('Y-m-d')}. Segera tindak lanjuti.",
                        'is_read' => false,
                    ]);

                    // Fire event for overdue CAPA
                    event(new CapaDeadlineApproaching($capa));

                    $overdueCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to process overdue CAPA", [
                        'capa_id' => $capa->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // --- 2. Process approaching deadline CAPAs ---
            $approaching = $capaService->getApproachingDeadline(7);
            $approachingCount = 0;

            foreach ($approaching as $capa) {
                try {
                    AgentPeringatanLog::create([
                        'prodi_id' => $capa->auditMutu?->prodi_id,
                        'dosen_id' => $capa->picUser?->dosen_id,
                        'jenis_peringatan' => 'capa_deadline',
                        'tingkat' => 'warning',
                        'pesan' => "CAPA #{$capa->id} mendekati deadline ({$capa->corrective_deadline->format('Y-m-d')}). Segera selesaikan tindakan korektif.",
                        'is_read' => false,
                    ]);

                    // Fire event for approaching deadline CAPA
                    event(new CapaDeadlineApproaching($capa));

                    $approachingCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to process approaching CAPA", [
                        'capa_id' => $capa->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info("CheckCapaDeadline job completed", [
                'overdue_processed' => $overdueCount,
                'approaching_processed' => $approachingCount,
            ]);
        } catch (\Exception $e) {
            Log::error("CheckCapaDeadline job failed", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
