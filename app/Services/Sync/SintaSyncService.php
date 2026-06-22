<?php

namespace App\Services\Sync;

use App\Models\Dosen;
use App\Models\IntegrasiSintaPenelitian;
use App\Models\IntegrasiSintaPkm;
use App\Models\IntegrasiSintaPublikasi;
use App\Models\Penelitian;
use App\Models\Pkm;
use App\Models\Publikasi;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Log;

class SintaSyncService
{
    public function __construct(private MCPClientService $mcp) {}

    public function sync(string $type = 'all', bool $dryRun = false): array
    {
        $result = ['pulled' => 0, 'updated' => 0, 'conflicts' => 0, 'status' => 'completed'];

        $dosenList = Dosen::whereNotNull('sinta_id')->where('is_active', true)->get();

        foreach ($dosenList as $dosen) {
            try {
                if ($type === 'all' || $type === 'publikasi') {
                    $pubResult = $this->syncPublikasi($dosen, $dryRun);
                    $result['pulled'] += $pubResult['pulled'];
                    $result['updated'] += $pubResult['updated'];
                    $result['conflicts'] += $pubResult['conflicts'];
                }

                if ($type === 'all' || $type === 'penelitian') {
                    $litResult = $this->syncPenelitian($dosen, $dryRun);
                    $result['pulled'] += $litResult['pulled'];
                    $result['updated'] += $litResult['updated'];
                    $result['conflicts'] += $litResult['conflicts'];
                }

                if ($type === 'all' || $type === 'pkm') {
                    $pkmResult = $this->syncPkm($dosen, $dryRun);
                    $result['pulled'] += $pkmResult['pulled'];
                    $result['updated'] += $pkmResult['updated'];
                    $result['conflicts'] += $pkmResult['conflicts'];
                }
            } catch (\Throwable $e) {
                Log::error("SintaSync: Failed for dosen {$dosen->id} ({$dosen->nidn}): {$e->getMessage()}");
            }
        }

        return $result;
    }

    public function syncSingleDosen(Dosen $dosen, string $type = 'all', bool $dryRun = false): array
    {
        $result = ['pulled' => 0, 'updated' => 0, 'conflicts' => 0];

        if (!$dosen->sinta_id) {
            return $result;
        }

        try {
            if ($type === 'all' || $type === 'publikasi') {
                $pubResult = $this->syncPublikasi($dosen, $dryRun);
                $result['pulled'] += $pubResult['pulled'];
                $result['updated'] += $pubResult['updated'];
                $result['conflicts'] += $pubResult['conflicts'];
            }

            if ($type === 'all' || $type === 'penelitian') {
                $litResult = $this->syncPenelitian($dosen, $dryRun);
                $result['pulled'] += $litResult['pulled'];
                $result['updated'] += $litResult['updated'];
                $result['conflicts'] += $litResult['conflicts'];
            }

            if ($type === 'all' || $type === 'pkm') {
                $pkmResult = $this->syncPkm($dosen, $dryRun);
                $result['pulled'] += $pkmResult['pulled'];
                $result['updated'] += $pkmResult['updated'];
                $result['conflicts'] += $pkmResult['conflicts'];
            }
        } catch (\Throwable $e) {
            Log::error("SintaSync single dosen failed for {$dosen->id}: {$e->getMessage()}");
        }

        return $result;
    }

    private function syncPublikasi(Dosen $dosen, bool $dryRun): array
    {
        try {
            $response = $this->mcp->fetchSintaPublications($dosen->sinta_id, [
                'year_from' => now()->subYears(5)->year,
                'fetch_all' => true,
            ]);
        } catch (\Throwable $e) {
            Log::warning("SintaSync: MCP unavailable for publikasi {$dosen->sinta_id}: {$e->getMessage()}");
            return ['pulled' => 0, 'updated' => 0, 'conflicts' => 0];
        }

        $publications = $response['publications'] ?? ($response['results'] ?? []);
        $pulled = count($publications);
        $updated = 0;
        $conflicts = 0;

        foreach ($publications as $pub) {
            if ($dryRun) continue;

            $title = $pub['judul_publikasi'] ?: ($pub['judul'] ?: ($pub['title'] ?: ''));
            if (empty($title)) continue;

            $existingPub = Publikasi::firstOrCreate(
                ['dosen_id' => $dosen->id, 'judul_publikasi' => $title],
                [
                    'prodi_id' => $dosen->prodi_id,
                    'jenis_publikasi' => $pub['jenis_publikasi'] ?? null,
                    'tahun' => $pub['tahun'] ?? null,
                    'link' => $pub['link'] ?? null,
                ]
            );

            IntegrasiSintaPublikasi::updateOrCreate(
                ['dosen_id' => $dosen->id, 'judul' => $title],
                [
                    'publikasi_id' => $existingPub->id,
                    'data_dari_sinta' => $pub,
                    'status_sinkron' => 'matched',
                ]
            );

            $updated++;
        }

        return compact('pulled', 'updated', 'conflicts');
    }

    private function syncPenelitian(Dosen $dosen, bool $dryRun): array
    {
        try {
            $response = $this->mcp->fetchSintaResearches($dosen->sinta_id, [
                'year_from' => now()->subYears(5)->year,
                'fetch_all' => true,
            ]);
        } catch (\Throwable $e) {
            Log::warning("SintaSync: Research endpoint unavailable for {$dosen->sinta_id}: {$e->getMessage()}");
            return ['pulled' => 0, 'updated' => 0, 'conflicts' => 0];
        }

        $research = $response['researches'] ?? ($response['results'] ?? []);
        $pulled = count($research);
        $updated = 0;
        $conflicts = 0;

        foreach ($research as $row) {
            if ($dryRun) continue;

            $title = $row['judul_penelitian'] ?: ($row['judul'] ?: ($row['title'] ?: ''));
            if (empty($title)) continue;

            $tahun = $row['tahun'] ?? null;
            $skema = $row['skema'] ?? null;
            $dana = $row['jumlah_dana'] ?? $row['dana'] ?? null;

            $existingLit = Penelitian::firstOrCreate(
                ['dosen_id' => $dosen->id, 'judul_penelitian' => $title],
                [
                    'prodi_id' => $dosen->prodi_id,
                    'jenis_penelitian' => $skema,
                    'tahun_pelaksanaan' => $tahun,
                    'sumber_dana' => $row['sumber_dana'] ?? null,
                    'jumlah_dana' => $dana,
                ]
            );

            IntegrasiSintaPenelitian::updateOrCreate(
                ['dosen_id' => $dosen->id, 'judul' => $title],
                [
                    'penelitian_id' => $existingLit->id,
                    'tahun' => $tahun,
                    'skema' => $skema,
                    'jumlah_dana' => $dana,
                    'data_dari_sinta' => $row,
                    'status_sinkron' => 'matched',
                ]
            );

            $updated++;
        }

        return compact('pulled', 'updated', 'conflicts');
    }

    private function syncPkm(Dosen $dosen, bool $dryRun): array
    {
        try {
            $response = $this->mcp->fetchSintaCommunityServices($dosen->sinta_id, [
                'year_from' => now()->subYears(5)->year,
                'fetch_all' => true,
            ]);
        } catch (\Throwable $e) {
            Log::warning("SintaSync: PKM endpoint unavailable for {$dosen->sinta_id}: {$e->getMessage()}");
            return ['pulled' => 0, 'updated' => 0, 'conflicts' => 0];
        }

        $services = $response['community_services'] ?? ($response['results'] ?? []);
        $pulled = count($services);
        $updated = 0;
        $conflicts = 0;

        foreach ($services as $row) {
            if ($dryRun) continue;

            $title = $row['judul_pkm'] ?: ($row['judul'] ?: ($row['title'] ?: ''));
            if (empty($title)) continue;

            $tahun = $row['tahun'] ?? null;
            $skema = $row['skema'] ?? $row['jenis_pkm'] ?? null;
            $dana = $row['jumlah_dana'] ?? $row['dana'] ?? null;

            $existingPkm = Pkm::firstOrCreate(
                ['dosen_id' => $dosen->id, 'judul_pkm' => $title],
                [
                    'prodi_id' => $dosen->prodi_id,
                    'jenis_pkm' => $row['jenis_pkm'] ?? $skema,
                    'lokasi' => $row['lokasi'] ?? null,
                    'tahun_pelaksanaan' => $tahun,
                    'sumber_dana' => $row['sumber_dana'] ?? null,
                    'jumlah_dana' => $dana,
                ]
            );

            IntegrasiSintaPkm::updateOrCreate(
                ['dosen_id' => $dosen->id, 'judul' => $title],
                [
                    'pkm_id' => $existingPkm->id,
                    'tahun' => $tahun,
                    'skema' => $skema,
                    'jumlah_dana' => $dana,
                    'data_dari_sinta' => $row,
                    'status_sinkron' => 'matched',
                ]
            );

            $updated++;
        }

        return compact('pulled', 'updated', 'conflicts');
    }
}
