<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Listeners;

use App\Events\AuditStatusChanged;
use App\Models\AuditHistory;
use App\Models\UsulanRkat;
use Illuminate\Support\Facades\Log;

class SyncAuditToRkat
{
    /**
     * Handle the event when an audit status changes.
     * We sync to RKAT when an audit is 'verified' or 'closed'.
     */
    public function handle(AuditStatusChanged $event): void
    {
        // Only trigger on important status changes
        if (!in_array($event->newStatus, ['verified', 'closed'])) {
            return;
        }

        $audit = $event->audit;
        
        // Detect if the finding is related to procurement or facilities
        if (!$this->isRkatRelated($audit->temuan . ' ' . $audit->judul_audit)) {
            return;
        }

        try {
            // Find recent approved RKAT for this prodi to add a note
            // or find draft RKAT to suggest an update.
            $rkat = UsulanRkat::where('prodi_id', $audit->prodi_id)
                ->where('periode_id', $audit->periode_id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($rkat) {
                $rkat->komentar_reviewer = ($rkat->komentar_reviewer ? $rkat->komentar_reviewer . "\n" : "") . 
                    "[AI Sync] Temuan audit #{$audit->id} terdeteksi relevan dengan usulan ini. " .
                    "Rekomendasi: {$audit->rekomendasi}";
                
                $rkat->save();

                AuditHistory::create([
                    'audit_mutu_id' => $audit->id,
                    'user_id' => $event->user->id,
                    'field' => 'rkat_sync',
                    'old_value' => null,
                    'new_value' => "Linked to RKAT usulan #{$rkat->id}",
                    'action' => 'rkat_integration_updated',
                ]);

                Log::info("Audit linked to RKAT usulan", [
                    'audit_id' => $audit->id,
                    'rkat_id' => $rkat->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to sync audit to RKAT", [
                'audit_id' => $audit->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Detect if the finding is related to budget/RKAT.
     */
    private function isRkatRelated(string $text): bool
    {
        $text = strtolower($text);
        
        $keywords = [
            'pengadaan', 'beli', 'pembelian', 'sarana', 'prasarana', 
            'investasi', 'alat', 'fasilitas', 'renovasi', 'perbaikan',
            'anggaran', 'biaya', 'dana', 'laboratorium', 'inventaris'
        ];

        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
