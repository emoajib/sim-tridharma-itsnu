<?php

namespace App\Listeners;

use App\Events\AuditStatusChanged;
use App\Models\AgentPeringatanLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SendPeringatanOnAuditAssignment
{
    /**
     * Create an AgentPeringatanLog entry when an audit is assigned to a PIC.
     */
    public function handle(AuditStatusChanged $event): void
    {
        // Only act when status transitions to 'assigned'
        if ($event->newStatus !== 'assigned') {
            return;
        }

        $audit = $event->audit;

        if (! $audit->pic_user_id) {
            Log::warning("Audit assigned but no PIC set", [
                'audit_id' => $audit->id,
            ]);

            return;
        }

        try {
            $pic = User::find($audit->pic_user_id);

            AgentPeringatanLog::create([
                'prodi_id' => $audit->prodi_id,
                'dosen_id' => $pic?->dosen_id,
                'jenis_peringatan' => 'audit_assignment',
                'tingkat' => 'warning',
                'pesan' => "Anda ditugaskan sebagai PIC untuk audit: {$audit->judul_audit}. Segera lakukan tindak lanjut.",
                'is_read' => false,
            ]);

            Log::info("Peringatan sent on audit assignment", [
                'audit_id' => $audit->id,
                'pic_user_id' => $audit->pic_user_id,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send peringatan on audit assignment", [
                'audit_id' => $audit->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
