<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Services\SPMI;

use App\Models\AuditMutu;
use App\Models\Edps;
use App\Models\Rtm;
use App\Models\RtmActionItem;
use Illuminate\Support\Facades\Log;

class RtmGeneratorService
{
    /**
     * Generate an RTM agenda draft automatically based on active period data.
     */
    public function generateDraft(int $prodiId, int $periodeId, int $userId): array
    {
        try {
            // 1. Fetch unaddressed audit findings
            $audits = AuditMutu::where('prodi_id', $prodiId)
                ->where('periode_id', $periodeId)
                ->whereIn('status', ['closed', 'verified'])
                ->get();

            // 2. Fetch EDPS that needs improvement (score < 80)
            $weakEdps = Edps::where('prodi_id', $prodiId)
                ->where('periode_id', $periodeId)
                ->where('capaian', '<', 80)
                ->get();

            if ($audits->isEmpty() && $weakEdps->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada temuan audit atau evaluasi diri yang perlu dibahas pada periode ini.',
                ];
            }

            // 3. Create RTM Header
            $rtm = Rtm::create([
                'prodi_id' => $prodiId,
                'periode_id' => $periodeId,
                'judul' => 'RTM Otomatis (Generated)',
                'tanggal_rapat' => now()->addDays(7)->format('Y-m-d'), // Schedule 1 week from now
                'notulensi' => "Draf notulensi otomatis dibuat oleh AI. Harap diperiksa dan dilengkapi:\n\nAgenda Utama:\n1. Pembahasan Temuan Audit Mutu Internal\n2. Pembahasan Evaluasi Diri Program Studi (EDPS) di bawah target.\n",
                'status' => 'draft',
                'created_by' => $userId,
            ]);

            // 4. Generate Action Items for Audits
            foreach ($audits as $audit) {
                RtmActionItem::create([
                    'rtm_id' => $rtm->id,
                    'deskripsi' => "Bahas Temuan Audit #{$audit->id}: {$audit->judul_audit}. \nTemuan: {$audit->temuan}\nRekomendasi: {$audit->rekomendasi}",
                    'pic_user_id' => null, // To be assigned in meeting
                    'deadline' => null,
                    'status' => 'open',
                ]);
            }

            // 5. Generate Action Items for EDPS
            foreach ($weakEdps as $edps) {
                RtmActionItem::create([
                    'rtm_id' => $rtm->id,
                    'deskripsi' => "Tindak Lanjut EDPS: Capaian Standar {$edps->standarMutu->nama_standar} hanya {$edps->capaian}% (Target: {$edps->target}). Analisis: {$edps->analisis}",
                    'pic_user_id' => null,
                    'deadline' => null,
                    'status' => 'open',
                ]);
            }

            Log::info('RTM Draft automatically generated', [
                'rtm_id' => $rtm->id,
                'prodi_id' => $prodiId,
                'periode_id' => $periodeId,
            ]);

            return [
                'success' => true,
                'data' => $rtm->load('actionItems'),
                'message' => 'Draf RTM dan Action Items berhasil di-generate secara otomatis.',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate RTM', [
                'error' => $e->getMessage(),
                'prodi_id' => $prodiId,
            ]);
            
            return [
                'success' => false,
                'message' => 'Gagal membuat draf RTM otomatis: ' . $e->getMessage(),
            ];
        }
    }
}
