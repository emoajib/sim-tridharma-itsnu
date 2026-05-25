<?php

namespace App\Services\SPMI;

use App\Events\CapaDeadlineApproaching;
use App\Models\AgentPeringatanLog;
use App\Models\AuditMutu;
use App\Models\AuditHistory;
use App\Models\Capa;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CapaService
{
    /**
     * Auto-create a CAPA from an audit finding for severity >= sedang.
     */
    public function createFromAudit(AuditMutu $audit): Capa
    {
        $severityOrder = ['ringan' => 1, 'sedang' => 2, 'berat' => 3, 'kritis' => 4];
        $severityLevel = $severityOrder[$audit->severity ?? 'ringan'] ?? 1;

        if ($severityLevel < 2) {
            throw new Exception("CAPA can only be created from findings with severity >= sedang");
        }

        return DB::transaction(function () use ($audit) {
            $existing = Capa::where('audit_mutu_id', $audit->id)->first();
            if ($existing) {
                throw new Exception("CAPA already exists for audit #{$audit->id}");
            }

            $capa = Capa::create([
                'audit_mutu_id' => $audit->id,
                'pic_user_id' => $audit->pic_user_id,
                'root_cause_analysis' => "Temuan: {$audit->temuan}\n\nRekomendasi: {$audit->rekomendasi}",
                'corrective_deadline' => now()->addDays(30),
                'status' => 'open',
            ]);

            // Log to audit history
            AuditHistory::create([
                'audit_mutu_id' => $audit->id,
                'user_id' => $audit->pic_user_id,
                'field' => 'capa_created',
                'old_value' => null,
                'new_value' => "CAPA #{$capa->id} created",
                'action' => 'capa_auto_created',
            ]);

            Log::info("CAPA auto-created from audit", [
                'audit_id' => $audit->id,
                'capa_id' => $capa->id,
            ]);

            return $capa;
        });
    }

    /**
     * Submit a CAPA for verification. Validates corrective_action and evidence are filled.
     */
    public function submitForVerification(Capa $capa, int $userId): void
    {
        if (empty($capa->corrective_action)) {
            throw new Exception("Corrective action must be filled before submission");
        }

        if (empty($capa->corrective_evidence_file)) {
            throw new Exception("Evidence file must be uploaded before submission");
        }

        DB::transaction(function () use ($capa, $userId) {
            Capa::where('id', $capa->id)->lockForUpdate()->first();

            $capa->status = 'awaiting_verification';
            $capa->save();

            AuditHistory::create([
                'audit_mutu_id' => $capa->audit_mutu_id,
                'user_id' => $userId,
                'field' => 'capa_status',
                'old_value' => 'in_progress',
                'new_value' => 'awaiting_verification',
                'action' => 'capa_submitted_for_verification',
            ]);

            Log::info("CAPA submitted for verification", [
                'capa_id' => $capa->id,
                'user_id' => $userId,
            ]);
        });
    }

    /**
     * Verify or reject a CAPA.
     */
    public function verify(Capa $capa, int $userId, string $note, bool $approved): void
    {
        DB::transaction(function () use ($capa, $userId, $note, $approved) {
            Capa::where('id', $capa->id)->lockForUpdate()->first();

            if ($approved) {
                $capa->status = 'verified';
                $capa->verified_by_user_id = $userId;
                $capa->verified_at = now();
                $capa->verification_note = $note;

                // Auto-lock the associated AuditMutu
                AuditMutu::where('id', $capa->audit_mutu_id)
                    ->where('is_locked', false)
                    ->update([
                        'is_locked' => true,
                        'locked_at' => now(),
                        'verification_note' => $note,
                        'verified_by' => $userId,
                        'verified_at' => now(),
                    ]);
            } else {
                $capa->status = 'rejected';
                $capa->verification_note = $note;
            }

            $capa->save();

            AuditHistory::create([
                'audit_mutu_id' => $capa->audit_mutu_id,
                'user_id' => $userId,
                'field' => 'capa_status',
                'old_value' => 'awaiting_verification',
                'new_value' => $capa->status,
                'action' => $approved ? 'capa_verified' : 'capa_rejected',
            ]);

            Log::info("CAPA {$capa->id} verification: " . ($approved ? 'approved' : 'rejected'), [
                'capa_id' => $capa->id,
                'user_id' => $userId,
            ]);
        });
    }

    /**
     * Get all overdue CAPAs (past deadline, not yet resolved).
     */
    public function getOverdue(): Collection
    {
        return Capa::where('corrective_deadline', '<', now())
            ->whereNotIn('status', ['verified', 'closed', 'archived'])
            ->with(['auditMutu.prodi', 'picUser'])
            ->get();
    }

    /**
     * Get CAPAs approaching their deadline within the specified number of days.
     */
    public function getApproachingDeadline(int $days = 7): Collection
    {
        return Capa::whereNotNull('corrective_deadline')
            ->where('corrective_deadline', '>=', now())
            ->where('corrective_deadline', '<=', now()->addDays($days))
            ->whereNotIn('status', ['verified', 'closed', 'archived'])
            ->with(['auditMutu.prodi', 'picUser'])
            ->get();
    }
}
