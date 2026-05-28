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
            } catch (\Throwable $e) {
                Log::error("SintaSync: Failed for dosen {$dosen->id} ({$dosen->nidn}): {$e->getMessage()}");
            }
        }

        return $result;
    }

    private function syncPublikasi(Dosen $dosen, bool $dryRun): array
    {
        try {
            $response = $this->mcp->callToolSync('integrasi_sync', [
                'sumber' => 'sinta',
                'author_id' => $dosen->sinta_id,
                'type' => 'publikasi',
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

            $title = $pub['title'] ?? $pub['judul'] ?? '';
            if (empty($title)) continue;

            $existing = IntegrasiSintaPublikasi::where('dosen_id', $dosen->id)
                ->where('judul', $title)
                ->first();

            if ($existing) {
                $existing->update([
                    'data_dari_sinta' => $pub,
                    'status_sinkron' => 'pending',
                ]);
                continue;
            }

            $existingPub = Publikasi::where('dosen_id', $dosen->id)
                ->where('judul_publikasi', $title)
                ->first();

            IntegrasiSintaPublikasi::create([
                'dosen_id' => $dosen->id,
                'publikasi_id' => $existingPub?->id,
                'judul' => $title,
                'data_dari_sinta' => $pub,
                'status_sinkron' => $existingPub ? 'matched' : 'pending',
            ]);

            $updated++;
        }

        return compact('pulled', 'updated', 'conflicts');
    }

    private function syncPenelitian(Dosen $dosen, bool $dryRun): array
    {
        try {
            $response = $this->mcp->callToolSync('integrasi_sync', [
                'sumber' => 'sinta',
                'author_id' => $dosen->sinta_id,
                'type' => 'penelitian',
            ]);
        } catch (\Throwable $e) {
            Log::warning("SintaSync: Research endpoint unavailable for {$dosen->sinta_id}: {$e->getMessage()}");
            return ['pulled' => 0, 'updated' => 0, 'conflicts' => 0];
        }

        $research = $response['research'] ?? ($response['results'] ?? []);
        $pulled = count($research);
        $updated = 0;
        $conflicts = 0;

        foreach ($research as $row) {
            if ($dryRun) continue;

            $title = $row['title'] ?? $row['judul'] ?? '';
            if (empty($title)) continue;

            $existing = IntegrasiSintaPenelitian::where('dosen_id', $dosen->id)
                ->where('judul', $title)
                ->first();

            if ($existing) {
                $existing->update(['data_dari_sinta' => $row, 'status_sinkron' => 'pending']);
                continue;
            }

            $existingLit = Penelitian::where('dosen_id', $dosen->id)
                ->where('judul_penelitian', $title)
                ->first();

            IntegrasiSintaPenelitian::create([
                'dosen_id' => $dosen->id,
                'penelitian_id' => $existingLit?->id,
                'judul' => $title,
                'tahun' => $row['year'] ?? $row['tahun'] ?? null,
                'skema' => $row['scheme'] ?? $row['skema'] ?? null,
                'jumlah_dana' => $row['fund'] ?? $row['jumlah_dana'] ?? null,
                'data_dari_sinta' => $row,
                'status_sinkron' => $existingLit ? 'matched' : 'pending',
            ]);

            $updated++;
        }

        return compact('pulled', 'updated', 'conflicts');
    }
}
