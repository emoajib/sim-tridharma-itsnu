<?php

namespace App\Services;

use App\Models\AgentPeringatanLog;
use App\Models\AgentPredictionHistory;
use App\Models\Bkd;
use App\Models\DokumenBukti;
use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\KegiatanPendidikan;
use App\Models\LembagaAkreditasi;
use App\Models\MahasiswaBimbingan;
use App\Models\Penelitian;
use App\Models\Penunjang;
use App\Models\PeriodeAkademik;
use App\Models\Pkm;
use App\Models\Prodi;
use App\Models\Publikasi;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getStats(): array
    {
        return [
            'dosen_count' => Dosen::count(),
            'prodi_count' => Prodi::count(),
            'fakultas_count' => Fakultas::count(),
        ];
    }

    public function getPortofolioStats(?int $periodeId): array
    {
        $query = fn ($q) => $periodeId ? $q->where('periode_id', $periodeId) : $q;

        return [
            'pendidikan_count' => $query(KegiatanPendidikan::query())->count(),
            'penelitian_count' => $query(Penelitian::query())->count(),
            'publikasi_count' => $query(Publikasi::query())->count(),
            'pkm_count' => $query(Pkm::query())->count(),
            'penunjang_count' => $query(Penunjang::query())->count(),
            'bkd_count' => $query(Bkd::query())->count(),
            'bimbingan_count' => $query(MahasiswaBimbingan::query())->count(),
            'dokumen_count' => DokumenBukti::count(),
        ];
    }

    public function getBkdStats(?int $periodeId): array
    {
        $query = fn ($q) => $periodeId ? $q->where('periode_id', $periodeId) : $q;

        $aggregates = (clone $query(Bkd::query()))
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as disetujui,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as diajukan,
                AVG(total_sks) as rata_sks
            ')
            ->addBinding(['disetujui', 'draft', 'diajukan'], 'select')
            ->first();

        return [
            'total' => (int) ($aggregates->total ?? 0),
            'disetujui' => (int) ($aggregates->disetujui ?? 0),
            'draft' => (int) ($aggregates->draft ?? 0),
            'diajukan' => (int) ($aggregates->diajukan ?? 0),
            'rata_sks' => (float) ($aggregates->rata_sks ?? 0),
        ];
    }

    public function getPeringatanStats(): array
    {
        $aggregates = AgentPeringatanLog::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN tingkat = 'critical' THEN 1 ELSE 0 END) as critical,
            SUM(CASE WHEN tingkat = 'warning' THEN 1 ELSE 0 END) as warning,
            SUM(CASE WHEN tingkat = 'info' THEN 1 ELSE 0 END) as info,
            SUM(CASE WHEN is_read = false THEN 1 ELSE 0 END) as unread
        ")->first();

        return [
            'critical' => (int) ($aggregates->critical ?? 0),
            'warning' => (int) ($aggregates->warning ?? 0),
            'info' => (int) ($aggregates->info ?? 0),
            'unread' => (int) ($aggregates->unread ?? 0),
            'total' => (int) ($aggregates->total ?? 0),
        ];
    }

    public function getProdiAccreditation($activeProdis, ?int $periodeId): array
    {
        $prodiIds = $activeProdis->pluck('id');

        $predictions = AgentPredictionHistory::whereIn('prodi_id', $prodiIds)
            ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
            ->selectRaw('prodi_id, MAX(created_at) as latest')
            ->groupBy('prodi_id')
            ->get()
            ->keyBy('prodi_id');

        return $activeProdis->map(function ($p) use ($predictions) {
            $latestSim = null;
            if (isset($predictions[$p->id])) {
                $latestSim = AgentPredictionHistory::where('prodi_id', $p->id)
                    ->where('created_at', $predictions[$p->id]->getAttribute('latest'))
                    ->first();
            }

            $trend = 0;
            if ($latestSim) {
                $older = AgentPredictionHistory::where('prodi_id', $p->id)
                    ->where('id', '!=', $latestSim->id)
                    ->latest()
                    ->skip(1)
                    ->first();
                if ($older && $older->skor_prediksi > 0) {
                    $trend = ($latestSim->skor_prediksi - $older->skor_prediksi) / $older->skor_prediksi;
                    $trend = max(-0.1, min(0.1, $trend));
                }
            }

            return [
                'id' => $p->id,
                'nama' => $p->nama_prodi,
                'fakultas' => $p->fakultas->nama_fakultas ?? '-',
                'status_saat_ini' => $p->akreditasi ?? 'Belum Terakreditasi',
                'skor_simulasi' => $latestSim ? $latestSim->skor_prediksi : 0,
                'trend' => round($trend, 4),
            ];
        })->toArray();
    }

    public function getInstitutionAccreditation(?int $instrumenId): ?array
    {
        $lembaga = LembagaAkreditasi::find($instrumenId);
        if (! $lembaga || $lembaga->singkatan !== 'BAN-PT') {
            return null;
        }

        $lastSync = AgentPredictionHistory::latest()->value('created_at');

        return [
            'nama' => Setting::get('institusi_nama', 'ITSNU Pekalongan'),
            'status_saat_ini' => Setting::get('aipt_status', 'Baik'),
            'skor_simulasi' => (float) Setting::get('aipt_sim_score', 0),
            'target' => Setting::get('aipt_target', 'Unggul'),
            'last_sync' => $lastSync ? $lastSync->format('d M Y') : '-',
        ];
    }

    public function getKriteriaStats(?int $instrumenId, ?int $periodeId): array
    {
        return DB::table('trx_pemenuhan_indikator as pi')
            ->join('m_indikator_akreditasi as i', 'i.id', '=', 'pi.indikator_id')
            ->join('m_instrumen_akreditasi as ins', 'ins.id', '=', 'i.instrumen_id')
            ->where('ins.lembaga_id', $instrumenId)
            ->select('i.kriteria as kode', DB::raw('AVG(pi.nilai) as skor'))
            ->when($periodeId, fn ($q) => $q->where('pi.periode_id', $periodeId))
            ->groupBy('i.kriteria')
            ->orderBy('i.kriteria')
            ->get()
            ->map(fn ($item) => [
                'kode' => $item->kode,
                'nama' => 'Kriteria '.$item->kode,
                'skor' => round((float) $item->skor, 2),
                'target' => 100,
            ])
            ->toArray();
    }

    public function getDefaultInstrumentId(): int
    {
        return (int) LembagaAkreditasi::where('singkatan', 'BAN-PT')->value('id');
    }

    public function getDefaultTab(): string
    {
        return Setting::get('dashboard_default_tab', 'overview');
    }

    public function getRecentPendidikan(?int $periodeId): Collection
    {
        return KegiatanPendidikan::with('dosen')
            ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
            ->latest()->take(5)->get();
    }

    public function getRecentPenelitian(?int $periodeId): Collection
    {
        return Penelitian::with('dosen')
            ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
            ->latest()->take(5)->get();
    }

    public function getLatestPrediction(): ?AgentPredictionHistory
    {
        return AgentPredictionHistory::latest()->first();
    }

    public function getFilterData(?int $instrumenId, ?int $periodeId): array
    {
        return [
            'activeProdis' => Prodi::where('lembaga_akreditasi_id', $instrumenId)
                ->with('fakultas')
                ->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
            'selectedPeriode' => $periodeId ? PeriodeAkademik::find($periodeId) : null,
            'lembaga_list' => LembagaAkreditasi::where('is_active', true)->get(),
        ];
    }
}
