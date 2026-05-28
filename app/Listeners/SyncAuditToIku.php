<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Listeners;

use App\Events\AuditStatusChanged;
use App\Models\AuditHistory;
use App\Models\CascadingIku;
use App\Models\IndikatorIku;
use Illuminate\Support\Facades\Log;

class SyncAuditToIku
{
    /**
     * Handle the event when an audit status changes.
     * We sync to IKU when an audit is 'verified' or 'closed'.
     */
    public function handle(AuditStatusChanged $event): void
    {
        // Only trigger on important status changes
        if (!in_array($event->newStatus, ['verified', 'closed'])) {
            return;
        }

        $audit = $event->audit;
        $ikuCode = $this->detectIkuFromText($audit->temuan . ' ' . $audit->judul_audit);

        if (!$ikuCode) {
            return;
        }

        try {
            // Find cascading IKU for this prodi and period
            $cascading = CascadingIku::whereHas('iku', function ($query) use ($ikuCode) {
                $query->where('kode_iku', $ikuCode);
            })
            ->where('unit_type', 'Prodi')
            ->where('unit_id', $audit->prodi_id)
            ->where('periode_id', $audit->periode_id)
            ->first();

            if ($cascading) {
                // Update catatan to include the audit reference
                $cascading->catatan = ($cascading->catatan ? $cascading->catatan . "\n" : "") . 
                    "[" . now()->format('Y-m-d') . "] Terkait temuan audit #{$audit->id}: {$audit->judul_audit} (Status: {$event->newStatus})";
                
                $cascading->save();

                AuditHistory::create([
                    'audit_mutu_id' => $audit->id,
                    'user_id' => $event->user->id,
                    'field' => 'iku_sync',
                    'old_value' => null,
                    'new_value' => "Synced to IKU: {$ikuCode}",
                    'action' => 'iku_integration_updated',
                ]);

                Log::info("Audit synced to IKU cascading", [
                    'audit_id' => $audit->id,
                    'iku_code' => $ikuCode,
                    'cascading_id' => $cascading->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to sync audit to IKU", [
                'audit_id' => $audit->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Simple keyword-based detection for IKU.
     */
    private function detectIkuFromText(string $text): ?string
    {
        $text = strtolower($text);
        
        $mapping = [
            'IKU 1' => ['pekerjaan layak', 'lulusan bekerja', 'masa tunggu', 'gaji pertama', 'wirausaha'],
            'IKU 2' => ['magang', 'kampus merdeka', 'mbkm', 'pertukaran mahasiswa', 'pengalaman luar kampus'],
            'IKU 3' => ['dosen luar kampus', 'tridharma luar', 'dosen industri'],
            'IKU 4' => ['praktisi mengajar', 'dosen praktisi', 'industri mengajar'],
            'IKU 5' => ['hasil kerja dosen', 'sitasi', 'penelitian masyarakat', 'penerapan iptek'],
            'IKU 6' => ['kerjasama mitra', 'mou internasional', 'mitra kelas dunia'],
            'IKU 7' => ['kelas kolaboratif', 'case method', 'project based learning', 'pembelajaran partisipatif'],
            'IKU 8' => ['akreditasi internasional', 'sertifikasi internasional', 'standar internasional'],
        ];

        foreach ($mapping as $code => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    return $code;
                }
            }
        }

        if (preg_match('/iku[- ]?([1-8])/', $text, $matches)) {
            return 'IKU ' . $matches[1];
        }

        return null;
    }
}
