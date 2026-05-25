<?php

namespace App\Services\SPMI;

use App\Events\AuditSevereFindingCreated;
use App\Events\AuditStatusChanged;
use App\Models\AgentPeringatanLog;
use App\Models\AuditMutu;
use App\Models\AuditHistory;
use App\Models\Capa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpmiWorkflowService
{
    const AUDIT_STATUS_FLOW = [
        'draft' => ['submitted'],
        'submitted' => ['assigned'],
        'assigned' => ['in_progress'],
        'in_progress' => ['awaiting_verification'],
        'awaiting_verification' => ['verified', 'rejected'],
        'verified' => ['closed'],
        'closed' => ['archived'],
        'rejected' => ['in_progress'],
        'archived' => [],
    ];

    const CAPA_STATUS_FLOW = [
        'draft' => ['open'],
        'open' => ['in_progress'],
        'in_progress' => ['awaiting_verification'],
        'awaiting_verification' => ['verified', 'rejected'],
        'verified' => ['closed'],
        'rejected' => ['in_progress'],
        'closed' => ['archived'],
        'archived' => [],
    ];

    /**
     * Check if a status transition is valid according to the state machine.
     */
    public function canTransition(string $currentStatus, string $targetStatus, string $type = 'audit'): bool
    {
        $flow = $type === 'capa' ? self::CAPA_STATUS_FLOW : self::AUDIT_STATUS_FLOW;

        if (! isset($flow[$currentStatus])) {
            return false;
        }

        return in_array($targetStatus, $flow[$currentStatus], true);
    }

    /**
     * Execute a status transition with full validation, audit log, and side effects.
     *
     * @throws \RuntimeException
     */
    public function transition(AuditMutu|Capa $entity, string $toStatus, int $userId, ?string $note = null): void
    {
        $type = $entity instanceof Capa ? 'capa' : 'audit';
        $oldStatus = $entity->status;

        // --- Idempotency check ---
        if ($oldStatus === $toStatus) {
            Log::warning("SpmiWorkflowService: transition skipped (already {$toStatus})", [
                'entity_type' => $type,
                'entity_id' => $entity->id,
            ]);

            return;
        }

        // --- Validate transition ---
        if (! $this->canTransition($oldStatus, $toStatus, $type)) {
            throw new \RuntimeException(
                "Invalid transition: {$oldStatus} → {$toStatus} for {$type} #{$entity->id}"
            );
        }

        // --- Lock check for audits ---
        if ($entity instanceof AuditMutu && $entity->is_locked) {
            throw new \RuntimeException("Audit #{$entity->id} is locked and cannot transition.");
        }

        DB::transaction(function () use ($entity, $toStatus, $userId, $note, $oldStatus, $type) {
            // Lock the row for safe concurrent access
            if ($entity instanceof AuditMutu) {
                AuditMutu::where('id', $entity->id)->lockForUpdate()->first();
            } else {
                Capa::where('id', $entity->id)->lockForUpdate()->first();
            }

            // Update status
            $entity->status = $toStatus;

            // Set timestamps based on destination status
            if ($toStatus === 'closed' && $entity instanceof AuditMutu) {
                $entity->closed_at = now();
            }

            if ($toStatus === 'verified' && $entity instanceof Capa) {
                $entity->verified_by_user_id = $userId;
                $entity->verified_at = now();
            }

            if ($note !== null) {
                if ($entity instanceof AuditMutu) {
                    $entity->verification_note = $note;
                } elseif ($entity instanceof Capa) {
                    $entity->verification_note = $note;
                }
            }

            $entity->save();

            // --- Log to AuditHistory ---
            AuditHistory::create([
                'audit_mutu_id' => $entity instanceof AuditMutu ? $entity->id : $entity->audit_mutu_id,
                'user_id' => $userId,
                'field' => 'status',
                'old_value' => $oldStatus,
                'new_value' => $toStatus,
                'action' => "status_transition_{$type}",
            ]);

            // --- Dispatch event ---
            if ($entity instanceof AuditMutu) {
                event(new AuditStatusChanged($entity, $oldStatus, $toStatus, User::find($userId)));

                // Auto-create risk register for severe findings (severity >= berat)
                $severityMap = ['ringan' => 1, 'sedang' => 2, 'berat' => 3, 'kritis' => 4];
                $currentSeverity = $severityMap[$entity->severity ?? 'ringan'] ?? 1;
                if ($currentSeverity >= 3) {
                    event(new AuditSevereFindingCreated($entity));
                }

                // Calculate prodi quality score when closed
                if ($toStatus === 'closed') {
                    try {
                        $analysisService = app(AuditAnalysisService::class);
                        $score = $analysisService->getProdiScore($entity->prodi_id, $entity->periode_id);
                        Log::info("Prodi quality score calculated", [
                            'prodi_id' => $entity->prodi_id,
                            'periode_id' => $entity->periode_id,
                            'score' => $score,
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to calculate prodi quality score", [
                            'error' => $e->getMessage(),
                            'audit_id' => $entity->id,
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Assign a PIC (Person In Charge) to an audit and create a notification log.
     */
    public function assignPIC(AuditMutu $audit, int $userId): void
    {
        DB::transaction(function () use ($audit, $userId) {
            AuditMutu::where('id', $audit->id)->lockForUpdate()->first();

            $audit->pic_user_id = $userId;
            $audit->save();

            // Create peringatan log for the assigned PIC
            AgentPeringatanLog::create([
                'prodi_id' => $audit->prodi_id,
                'dosen_id' => User::find($userId)?->dosen_id,
                'jenis_peringatan' => 'audit_assignment',
                'tingkat' => 'info',
                'pesan' => "Anda ditugaskan sebagai PIC untuk audit: {$audit->judul_audit}",
                'is_read' => false,
            ]);

            // Log assignment to history
            AuditHistory::create([
                'audit_mutu_id' => $audit->id,
                'user_id' => $userId,
                'field' => 'pic_user_id',
                'old_value' => null,
                'new_value' => (string) $userId,
                'action' => 'pic_assigned',
            ]);

            Log::info("PIC assigned to audit", [
                'audit_id' => $audit->id,
                'pic_user_id' => $userId,
            ]);
        });
    }
}
