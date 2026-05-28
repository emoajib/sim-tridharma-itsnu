<?php

namespace App\Services\Sync;

use App\Models\Dosen;
use App\Models\IntegrasiPddiktiDosen;
use App\Models\Prodi;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Log;

class PddiktiSyncService
{
    public function __construct(private MCPClientService $mcp) {}

    public function sync(string $type = 'all', bool $dryRun = false): array
    {
        $result = ['pulled' => 0, 'updated' => 0, 'conflicts' => 0, 'status' => 'completed'];

        if ($type === 'all' || $type === 'dosen') {
            $dosenResult = $this->syncDosen($dryRun);
            $result['pulled'] += $dosenResult['pulled'];
            $result['updated'] += $dosenResult['updated'];
            $result['conflicts'] += $dosenResult['conflicts'];
        }

        if ($type === 'all' || $type === 'prodi') {
            $prodiResult = $this->syncProdi($dryRun);
            $result['pulled'] += $prodiResult['pulled'];
            $result['updated'] += $prodiResult['updated'];
        }

        return $result;
    }

    public function syncDosen(bool $dryRun = false): array
    {
        $pulled = 0;
        $updated = 0;
        $conflicts = 0;

        $prodiList = Prodi::where('is_active', true)->get();

        foreach ($prodiList as $prodi) {
            try {
                $response = $this->mcp->fetchPddiktiDosen([
                    'prodi_id' => $prodi->kode_prodi,
                    'fetch_all' => true,
                ]);

                $dosenList = $response['results'] ?? [];

                foreach ($dosenList as $dosenData) {
                    $pulled++;

                    if ($dryRun) {
                        Log::info("[DRY-RUN] PddiktiSync: Would process NIDN {$dosenData['nidn']}");
                        continue;
                    }

                    $nidn = $dosenData['nidn'] ?? '';
                    if (empty($nidn)) {
                        continue;
                    }

                    $existing = Dosen::where('nidn', $nidn)->first();

                    if ($existing) {
                        $conflictsData = $this->detectConflicts($existing, $dosenData);
                        if (!empty($conflictsData)) {
                            IntegrasiPddiktiDosen::updateOrCreate(
                                ['dosen_id' => $existing->id, 'nidn' => $existing->nidn],
                                [
                                    'data_dari_pddikti' => $dosenData,
                                    'data_di_sistem' => $existing->toArray(),
                                    'status_sinkron' => 'conflict',
                                ]
                            );
                            $conflicts++;
                            continue;
                        }

                        $mapped = $this->mapPddiktiToDosen($dosenData);
                        $updateData = array_filter($mapped, fn($v) => !is_null($v) && $v !== '');
                        if (!empty($updateData)) {
                            $existing->update($updateData);
                            $updated++;
                        }
                    } else {
                        $mapped = $this->mapPddiktiToDosen($dosenData);
                        $mapped['prodi_id'] = $prodi->id;
                        $mapped['sinta_id'] = $dosenData['sinta_id'] ?? null;
                        Dosen::create($mapped);
                        $updated++;
                    }
                }
            } catch (\Throwable $e) {
                Log::error("PDDiktiSync: Failed for prodi {$prodi->kode_prodi}: {$e->getMessage()}");
            }
        }

        return compact('pulled', 'updated', 'conflicts');
    }

    public function syncProdi(bool $dryRun = false): array
    {
        $pulled = 0;
        $updated = 0;

        try {
            $response = $this->mcp->fetchPddiktiProdi(['fetch_all' => true]);
            $prodiList = $response['results'] ?? [];

            foreach ($prodiList as $prodiData) {
                $pulled++;
                if ($dryRun) {
                    Log::info("[DRY-RUN] PddiktiSync: Would process prodi {$prodiData['kode_prodi']}");
                    continue;
                }

                $kodeProdi = $prodiData['kode_prodi'] ?? '';
                if (empty($kodeProdi)) {
                    continue;
                }

                $existing = Prodi::where('kode_prodi', $kodeProdi)->first();
                $mapped = $this->mapPddiktiToProdi($prodiData);

                if ($existing) {
                    $updateData = array_filter($mapped, fn($v) => !is_null($v) && $v !== '');
                    if (!empty($updateData)) {
                        $existing->update($updateData);
                        $updated++;
                    }
                } else {
                    Prodi::create($mapped);
                    $updated++;
                }
            }
        } catch (\Throwable $e) {
            Log::error("PDDiktiSync: syncProdi failed: {$e->getMessage()}");
        }

        return compact('pulled', 'updated');
    }

    private function detectConflicts(Dosen $existing, array $pddiktiData): array
    {
        $conflicts = [];
        $fields = ['nama_depan', 'nama_belakang', 'gelar_depan', 'gelar_belakang', 'tempat_lahir', 'tanggal_lahir', 'email'];

        foreach ($fields as $field) {
            $pddiktiValue = $pddiktiData[$field] ?? null;
            $existingValue = $existing->{$field};

            if (!empty($pddiktiValue) && !empty($existingValue) && $pddiktiValue !== $existingValue) {
                $conflicts[$field] = [
                    'sistem' => $existingValue,
                    'pddikti' => $pddiktiValue,
                ];
            }
        }

        return $conflicts;
    }

    private function mapPddiktiToDosen(array $data): array
    {
        return [
            'nidn' => $data['nidn'] ?? '',
            'nip' => $data['nip'] ?? null,
            'nuptk' => $data['nuptk'] ?? null,
            'nama_depan' => $data['nama_depan'] ?: ($data['nama'] ?? ''),
            'nama_belakang' => $data['nama_belakang'] ?? null,
            'gelar_depan' => $data['gelar_depan'] ?? null,
            'gelar_belakang' => $data['gelar_belakang'] ?? null,
            'tempat_lahir' => $data['tempat_lahir'] ?? null,
            'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
            'email' => $data['email'] ?? null,
            'telepon' => $data['telepon'] ?? null,
            'status_aktivitas' => $data['status_aktivitas'] ?? 'aktif',
            'status_pegawai' => $data['status_pegawai'] ?? null,
            'ikatan_kerja' => $data['ikatan_kerja'] ?? null,
            'pendidikan_terakhir' => $data['pendidikan_terakhir'] ?? null,
            'jabatan_fungsional' => $data['jabatan_fungsional'] ?? null,
            'is_active' => true,
        ];
    }

    private function mapPddiktiToProdi(array $data): array
    {
        return [
            'kode_prodi' => $data['kode_prodi'] ?? '',
            'nama_prodi' => $data['nama_prodi'] ?? '',
            'jenjang' => $data['jenjang'] ?? null,
            'akreditasi' => $data['akreditasi'] ?? null,
            'sk_akreditasi' => $data['sk_akreditasi'] ?? null,
            'tanggal_kadaluarsa' => $data['tanggal_kadaluarsa'] ?? null,
            'is_active' => true,
        ];
    }
}
