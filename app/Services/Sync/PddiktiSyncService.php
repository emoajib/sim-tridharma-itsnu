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
                $response = $this->mcp->callToolSync('integrasi_sync', [
                    'sumber' => 'pddikti',
                    'prodi_id' => $prodi->kode_prodi,
                ]);

                $dosenList = $response['results'] ?? [];

                foreach ($dosenList as $dosenData) {
                    $pulled++;

                    if ($dryRun) {
                        Log::info("[DRY-RUN] PddiktiSync: Would process NIDN {$dosenData['nidn']}");
                        continue;
                    }

                    $existing = Dosen::where('nidn', $dosenData['nidn'] ?? '')->first();

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

                        $existing->update($this->mapPddiktiToDosen($dosenData));
                        $updated++;
                    } else {
                        Dosen::create($this->mapPddiktiToDosen($dosenData));
                        $updated++;
                    }
                }
            } catch (\Throwable $e) {
                Log::error("PDDiktiSync: Failed for prodi {$prodi->kode_prodi}: {$e->getMessage()}");
            }
        }

        return compact('pulled', 'updated', 'conflicts');
    }

    private function detectConflicts(Dosen $existing, array $pddiktiData): array
    {
        $conflicts = [];
        $fields = ['nama_depan' => 'nama_depan', 'gelar_depan' => 'gelar_depan', 'gelar_belakang' => 'gelar_belakang'];

        foreach ($fields as $pddiktiField => $dosenField) {
            $pddiktiValue = $pddiktiData[$pddiktiField] ?? null;
            $existingValue = $existing->{$dosenField};

            if ($pddiktiValue && $existingValue && $pddiktiValue !== $existingValue) {
                $conflicts[$dosenField] = [
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
            'nama_depan' => $data['nama_depan'] ?? $data['nama'] ?? '',
            'nama_belakang' => $data['nama_belakang'] ?? null,
            'gelar_depan' => $data['gelar_depan'] ?? null,
            'gelar_belakang' => $data['gelar_belakang'] ?? null,
            'tempat_lahir' => $data['tempat_lahir'] ?? null,
            'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
            'email' => $data['email'] ?? null,
            'telepon' => $data['telepon'] ?? null,
            'status_aktivitas' => $data['status_aktivitas'] ?? 'aktif',
            'is_active' => true,
        ];
    }
}
