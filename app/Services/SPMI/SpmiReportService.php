<?php

namespace App\Services\SPMI;

use App\Models\AuditMutu;
use App\Models\Capa;
use App\Models\Edps;
use App\Models\Prodi;
use App\Models\StandarMutu;
use App\Models\SurveySpmi;
use App\Models\SpmiDokumen;
use App\Models\SpmiCycle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpmiReportService
{
    const CACHE_TTL = 600; // 10 minutes for reports

    /**
     * Generate the comprehensive SPMI report (Laporan SPMI) for a given period.
     * Aggregates all SPMI data into a structured array for LLDIKTI submission.
     */
    public function generateLaporanSpmi(int $periodeId): array
    {
        $cacheKey = "spmi_report_{$periodeId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($periodeId) {
            return DB::transaction(function () use ($periodeId) {
                // 1. Standar Mutu data
                $standars = StandarMutu::where('is_active', true)->get();

                // 2. Audit findings aggregated by standar
                $auditByStandar = AuditMutu::where('periode_id', $periodeId)
                    ->selectRaw('
                        standar_mutu_id,
                        COUNT(*) as total_temuan,
                        SUM(CASE WHEN severity = "kritis" THEN 1 ELSE 0 END) as kritis,
                        SUM(CASE WHEN severity = "berat" THEN 1 ELSE 0 END) as berat,
                        SUM(CASE WHEN severity = "sedang" THEN 1 ELSE 0 END) as sedang,
                        SUM(CASE WHEN severity = "ringan" THEN 1 ELSE 0 END) as ringan,
                        SUM(CASE WHEN status IN ("closed","archived") THEN 1 ELSE 0 END) as closed
                    ')
                    ->groupBy('standar_mutu_id')
                    ->get()
                    ->keyBy('standar_mutu_id');

                $standarCapaian = [];
                foreach ($standars as $standar) {
                    $auditData = $auditByStandar->get($standar->id);
                    $total = (int) ($auditData->total_temuan ?? 0);
                    $closedCount = (int) ($auditData->closed ?? 0);
                    $standarCapaian[] = [
                        'kode_standar' => $standar->kode_standar,
                        'nama_standar' => $standar->nama_standar,
                        'kategori' => $standar->kategori,
                        'target_nilai' => $standar->target_nilai,
                        'total_temuan' => $total,
                        'kritis' => (int) ($auditData->kritis ?? 0),
                        'berat' => (int) ($auditData->berat ?? 0),
                        'sedang' => (int) ($auditData->sedang ?? 0),
                        'ringan' => (int) ($auditData->ringan ?? 0),
                        'closed' => $closedCount,
                        'close_rate' => $total > 0 ? round(($closedCount / $total) * 100, 1) : 100,
                    ];
                }

                // 3. EDPS data aggregated
                $edpsData = Edps::where('periode_id', $periodeId)
                    ->selectRaw('
                        standar_mutu_id,
                        AVG(target) as avg_target,
                        AVG(capaian) as avg_capaian,
                        COUNT(*) as total_entries
                    ')
                    ->groupBy('standar_mutu_id')
                    ->get()
                    ->keyBy('standar_mutu_id');

                $edpsCapaian = [];
                foreach ($standars as $standar) {
                    $edps = $edpsData->get($standar->id);
                    $edpsCapaian[] = [
                        'kode_standar' => $standar->kode_standar,
                        'avg_target' => (float) ($edps->avg_target ?? 0),
                        'avg_capaian' => (float) ($edps->avg_capaian ?? 0),
                        'gap' => round((float) (($edps->avg_target ?? 0) - ($edps->avg_capaian ?? 0)), 2),
                        'total_entries' => (int) ($edps->total_entries ?? 0),
                    ];
                }

                // 4. CAPA data
                $capaSummary = Capa::whereHas('auditMutu', fn ($q) => $q->where('periode_id', $periodeId))
                    ->selectRaw("
                        COUNT(*) as total_capa,
                        SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified,
                        SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                        SUM(CASE WHEN status IN ('open','in_progress','awaiting_verification') THEN 1 ELSE 0 END) as open
                    ")
                    ->first();

                // 5. Survey data
                $surveySummary = SurveySpmi::where('periode_id', $periodeId)
                    ->selectRaw("
                        COUNT(*) as total_survey,
                        AVG(skor_rata_rata) as avg_skor,
                        COUNT(DISTINCT responden_type) as responden_types
                    ")
                    ->first();

                // 6. SPMI Document status
                $dokumenSummary = SpmiDokumen::selectRaw("
                        COUNT(*) as total_dokumen,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
                        SUM(CASE WHEN tanggal_kadaluarsa < CURDATE() THEN 1 ELSE 0 END) as expired
                    ")
                    ->first();

                // 7. Cycle progress
                $activeCycle = SpmiCycle::where('status', 'active')->first();
                $cycles = SpmiCycle::orderBy('created_at', 'desc')
                    ->take(5)
                    ->get(['tahap', 'nama_siklus', 'persentase_selesai', 'status']);

                return [
                    'periode_id' => $periodeId,
                    'generated_at' => now()->toIso8601String(),
                    'standar_capaian' => $standarCapaian,
                    'edps_capaian' => $edpsCapaian,
                    'capa_summary' => [
                        'total_capa' => (int) ($capaSummary->total_capa ?? 0),
                        'verified' => (int) ($capaSummary->verified ?? 0),
                        'closed' => (int) ($capaSummary->closed ?? 0),
                        'rejected' => (int) ($capaSummary->rejected ?? 0),
                        'open' => (int) ($capaSummary->open ?? 0),
                    ],
                    'survey_summary' => [
                        'total_survey' => (int) ($surveySummary->total_survey ?? 0),
                        'avg_skor' => round((float) ($surveySummary->avg_skor ?? 0), 2),
                        'responden_types' => (int) ($surveySummary->responden_types ?? 0),
                    ],
                    'dokumen_summary' => [
                        'total_dokumen' => (int) ($dokumenSummary->total_dokumen ?? 0),
                        'active' => (int) ($dokumenSummary->active ?? 0),
                        'draft' => (int) ($dokumenSummary->draft ?? 0),
                        'expired' => (int) ($dokumenSummary->expired ?? 0),
                    ],
                    'cycle_info' => [
                        'active_cycle' => $activeCycle?->nama_siklus,
                        'active_cycle_progress' => $activeCycle?->persentase_selesai,
                        'recent_cycles' => $cycles->toArray(),
                    ],
                    'total_standar' => $standars->count(),
                    'prodi_count' => Prodi::where('is_active', true)->count(),
                ];
            });
        });
    }

    /**
     * Get PD Dikti reporting data for the specified period.
     */
    public function getPelaporanPddikti(int $periodeId): array
    {
        $cacheKey = "spmi_pddikti_report_{$periodeId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($periodeId) {
            $prodis = Prodi::where('is_active', true)->get(['id', 'kode_prodi', 'nama_prodi', 'jenjang']);

            $prodiData = [];
            foreach ($prodis as $prodi) {
                $auditCount = AuditMutu::where('prodi_id', $prodi->id)
                    ->where('periode_id', $periodeId)
                    ->count();

                $edpsCount = Edps::where('prodi_id', $prodi->id)
                    ->where('periode_id', $periodeId)
                    ->count();

                $prodiData[] = [
                    'kode_prodi' => $prodi->kode_prodi,
                    'nama_prodi' => $prodi->nama_prodi,
                    'jenjang' => $prodi->jenjang,
                    'audit_count' => $auditCount,
                    'edps_count' => $edpsCount,
                ];
            }

            return [
                'periode_id' => $periodeId,
                'generated_at' => now()->toIso8601String(),
                'total_prodi' => $prodis->count(),
                'prodi_list' => $prodiData,
                'total_audit' => AuditMutu::where('periode_id', $periodeId)->count(),
                'total_capa' => Capa::whereHas('auditMutu', fn ($q) => $q->where('periode_id', $periodeId))->count(),
                'total_edps' => Edps::where('periode_id', $periodeId)->count(),
                'total_survey' => SurveySpmi::where('periode_id', $periodeId)->count(),
            ];
        });
    }
}
