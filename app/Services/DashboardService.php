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
    private function applyScope($query, array $scopeParams): void
    {
        if (! empty($scopeParams['dosen_id'])) {
            if ($query->getModel() instanceof Dosen) {
                $query->where('id', $scopeParams['dosen_id']);
            } elseif ($query->getModel() instanceof Prodi) {
                $query->whereHas('dosens', fn ($q) => $q->where('id', $scopeParams['dosen_id']));
            } else {
                $query->where('dosen_id', $scopeParams['dosen_id']);
            }
        } elseif (! empty($scopeParams['prodi_id'])) {
            if ($query->getModel() instanceof Prodi) {
                $query->where('id', $scopeParams['prodi_id']);
            } elseif ($query->getModel() instanceof Dosen) {
                $query->where('prodi_id', $scopeParams['prodi_id']);
            } elseif ($query->getModel() instanceof AgentPeringatanLog || $query->getModel() instanceof DokumenBukti) {
                $query->where(function ($q) use ($scopeParams) {
                    $q->where('prodi_id', $scopeParams['prodi_id'])
                        ->orWhereHas('dosen', fn ($q2) => $q2->where('prodi_id', $scopeParams['prodi_id']));
                });
            } else {
                $query->whereHas('dosen', fn ($q) => $q->where('prodi_id', $scopeParams['prodi_id']));
            }
        } elseif (! empty($scopeParams['fakultas_id'])) {
            if ($query->getModel() instanceof Prodi) {
                $query->where('fakultas_id', $scopeParams['fakultas_id']);
            } elseif ($query->getModel() instanceof Dosen) {
                $query->whereHas('prodi', fn ($q) => $q->where('fakultas_id', $scopeParams['fakultas_id']));
            } elseif ($query->getModel() instanceof AgentPeringatanLog || $query->getModel() instanceof DokumenBukti) {
                $query->where(function ($q) use ($scopeParams) {
                    $q->whereHas('prodi', fn ($q2) => $q2->where('fakultas_id', $scopeParams['fakultas_id']))
                        ->orWhereHas('dosen.prodi', fn ($q2) => $q2->where('fakultas_id', $scopeParams['fakultas_id']));
                });
            } else {
                $query->whereHas('dosen.prodi', fn ($q) => $q->where('fakultas_id', $scopeParams['fakultas_id']));
            }
        }
    }

    public function getStats(array $scopeParams = []): array
    {
        $dosenQuery = Dosen::query();
        $prodiQuery = Prodi::query();
        $fakultasQuery = Fakultas::query();

        $this->applyScope($dosenQuery, $scopeParams);
        $this->applyScope($prodiQuery, $scopeParams);
        // Fakultas count is usually global or filtered by fakultas_id
        if (! empty($scopeParams['fakultas_id'])) {
            $fakultasQuery->where('id', $scopeParams['fakultas_id']);
        }

        // Single query with 3 subqueries → 1 round trip instead of 3
        $dosenSql = $dosenQuery->select(DB::raw('COUNT(*)'))->toSql();
        $dosenBindings = $dosenQuery->getBindings();
        $prodiSql = $prodiQuery->select(DB::raw('COUNT(*)'))->toSql();
        $prodiBindings = $prodiQuery->getBindings();
        $fakultasSql = $fakultasQuery->select(DB::raw('COUNT(*)'))->toSql();
        $fakultasBindings = $fakultasQuery->getBindings();

        $row = DB::selectOne("
            SELECT ({$dosenSql}) as dosen_count,
                   ({$prodiSql}) as prodi_count,
                   ({$fakultasSql}) as fakultas_count
        ", array_merge($dosenBindings, $prodiBindings, $fakultasBindings))
            ?? (object) ['dosen_count' => 0, 'prodi_count' => 0, 'fakultas_count' => 0];

        return [
            'dosen_count' => (int) $row->dosen_count,
            'prodi_count' => (int) $row->prodi_count,
            'fakultas_count' => (int) $row->fakultas_count,
        ];
    }

    public function getPortofolioStats(?int $periodeId, array $scopeParams = []): array
    {
        $query = function ($q) use ($periodeId, $scopeParams) {
            if ($periodeId) {
                $q->where('periode_id', $periodeId);
            }
            $this->applyScope($q, $scopeParams);

            return $q;
        };

        $dokumenQuery = DokumenBukti::query();
        $this->applyScope($dokumenQuery, $scopeParams);

        return [
            'pendidikan_count' => $query(KegiatanPendidikan::query())->count(),
            'penelitian_count' => $query(Penelitian::query())->count(),
            'publikasi_count' => $query(Publikasi::query())->count(),
            'pkm_count' => $query(Pkm::query())->count(),
            'penunjang_count' => $query(Penunjang::query())->count(),
            'bkd_count' => $query(Bkd::query())->count(),
            'bimbingan_count' => $query(MahasiswaBimbingan::query())->count(),
            'dokumen_count' => $dokumenQuery->count(),
        ];
    }

    public function getBkdStats(?int $periodeId, array $scopeParams = []): array
    {
        $query = function ($q) use ($periodeId, $scopeParams) {
            if ($periodeId) {
                $q->where('periode_id', $periodeId);
            }
            $this->applyScope($q, $scopeParams);

            return $q;
        };

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

    public function getPeringatanStats(array $scopeParams = []): array
    {
        $query = AgentPeringatanLog::query();
        $this->applyScope($query, $scopeParams);

        $aggregates = (clone $query)->selectRaw("
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

        if ($prodiIds->isEmpty()) {
            return [];
        }

        $ids = $prodiIds->toArray();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Build optional periode filter
        $periodeCondition = $periodeId ? ' AND aph.periode_id = ?' : '';
        $bindings = $periodeId ? array_merge($ids, [$periodeId]) : $ids;

        // === Batch 1: Latest prediction per prodi (DISTINCT ON — 1 query, not N) ===
        $latestPredictions = DB::select("
            SELECT DISTINCT ON (aph.prodi_id)
                   aph.prodi_id, aph.id, aph.skor_prediksi,
                   aph.probabilitas_unggul, aph.probabilitas_baik_sekali
            FROM agent_prediction_history aph
            WHERE aph.prodi_id IN ({$placeholders})
              AND aph.deleted_at IS NULL
              {$periodeCondition}
            ORDER BY aph.prodi_id, aph.created_at DESC
        ", $bindings);

        // === Batch 2: Previous predictions for trend (window function — 1 query, not N) ===
        $trends = DB::select("
            SELECT prodi_id, skor_prediksi,
                   LAG(skor_prediksi) OVER (PARTITION BY prodi_id ORDER BY created_at) AS previous_score
            FROM (
                SELECT aph.prodi_id, aph.skor_prediksi, aph.created_at,
                       ROW_NUMBER() OVER (PARTITION BY aph.prodi_id ORDER BY aph.created_at DESC) AS rn
                FROM agent_prediction_history aph
                WHERE aph.prodi_id IN ({$placeholders})
                  AND aph.deleted_at IS NULL
                  {$periodeCondition}
            ) ranked WHERE rn <= 2
        ", $bindings);

        $predMap = collect($latestPredictions)->keyBy('prodi_id');
        $trendMap = collect($trends)->groupBy('prodi_id');

        return $activeProdis->map(function ($p) use ($predMap, $trendMap) {
            $latest = $predMap->get($p->id);

            $trend = 0;
            if ($latest && $trendMap->has($p->id)) {
                $rows = $trendMap->get($p->id);
                // The row with a non-null previous_score is the newest (rn=1),
                // where LAG retrieved the immediately older score
                $rowWithPrev = $rows->first(fn ($row) => $row->previous_score !== null);
                if ($rowWithPrev && (float) $rowWithPrev->previous_score > 0) {
                    $diff = (float) $rowWithPrev->skor_prediksi - (float) $rowWithPrev->previous_score;
                    $trend = $diff / (float) $rowWithPrev->previous_score;
                    $trend = max(-0.1, min(0.1, $trend));
                }
            }

            return [
                'id' => $p->id,
                'nama' => $p->nama_prodi,
                'fakultas' => $p->fakultas->nama_fakultas ?? '-',
                'status_saat_ini' => $p->akreditasi ?? 'Belum Terakreditasi',
                'skor_simulasi' => $latest ? (float) $latest->skor_prediksi : 0,
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

    public function getKriteriaStats(?int $instrumenId, ?int $periodeId, array $scopeParams = []): array
    {
        $query = DB::table('trx_pemenuhan_indikator as pi')
            ->join('m_indikator_akreditasi as i', 'i.id', '=', 'pi.indikator_id')
            ->join('m_instrumen_akreditasi as ins', 'ins.id', '=', 'i.instrumen_id')
            ->where('ins.lembaga_id', $instrumenId)
            ->select('i.kriteria as kode', DB::raw('AVG(pi.nilai) as skor'))
            ->when($periodeId, fn ($q) => $q->where('pi.periode_id', $periodeId));

        if (! empty($scopeParams['prodi_id'])) {
            $query->where('pi.prodi_id', $scopeParams['prodi_id']);
        } elseif (! empty($scopeParams['fakultas_id'])) {
            $query->join('m_prodi as p', 'p.id', '=', 'pi.prodi_id')
                ->where('p.fakultas_id', $scopeParams['fakultas_id']);
        }

        return $query->groupBy('i.kriteria')
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

    public function getRecentPendidikan(?int $periodeId, array $scopeParams = []): Collection
    {
        $query = KegiatanPendidikan::with('dosen')
            ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId));

        $this->applyScope($query, $scopeParams);

        return $query->latest()->take(5)->get();
    }

    public function getRecentPenelitian(?int $periodeId, array $scopeParams = []): Collection
    {
        $query = Penelitian::with('dosen')
            ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId));

        $this->applyScope($query, $scopeParams);

        return $query->latest()->take(5)->get();
    }

    public function getLatestPrediction(array $scopeParams = []): ?AgentPredictionHistory
    {
        $query = AgentPredictionHistory::query();
        if (! empty($scopeParams['prodi_id'])) {
            $query->where('prodi_id', $scopeParams['prodi_id']);
        } elseif (! empty($scopeParams['fakultas_id'])) {
            $query->whereHas('prodi', fn ($q) => $q->where('fakultas_id', $scopeParams['fakultas_id']));
        }

        return $query->latest()->first();
    }

    public function getFilterData(?int $instrumenId, ?int $periodeId, array $scopeParams = []): array
    {
        $prodiQuery = Prodi::where('lembaga_akreditasi_id', $instrumenId)
            ->with('fakultas');

        if (! empty($scopeParams['prodi_id'])) {
            $prodiQuery->where('id', $scopeParams['prodi_id']);
        } elseif (! empty($scopeParams['fakultas_id'])) {
            $prodiQuery->where('fakultas_id', $scopeParams['fakultas_id']);
        }

        return [
            'activeProdis' => $prodiQuery->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
            'selectedPeriode' => $periodeId ? PeriodeAkademik::find($periodeId) : null,
            'lembaga_list' => LembagaAkreditasi::where('is_active', true)->get(),
        ];
    }
}
