<?php

namespace App\Services\Sync;

use App\Models\IntegrasiLogSinkron;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Log;

class SyncOrchestrator
{
    private array $services = [];

    public function __construct(
        private PddiktiSyncService $pddikti,
        private SintaSyncService $sinta,
        private MCPClientService $mcp,
    ) {
        $this->services = [
            'pddikti' => $this->pddikti,
            'sinta' => $this->sinta,
        ];
    }

    public function run(string $source = 'all', string $type = 'all', bool $dryRun = false): array
    {
        $startTime = microtime(true);
        $results = [];

        $sourcesToRun = $source === 'all' ? array_keys($this->services) : [$source];

        foreach ($sourcesToRun as $src) {
            if (!isset($this->services[$src])) {
                Log::warning("SyncOrchestrator: Unknown source '{$src}'");
                continue;
            }

            try {
                $result = $this->services[$src]->sync($type, $dryRun);
                $results[$src] = $result;

                IntegrasiLogSinkron::create([
                    'sumber' => $src,
                    'jenis_data' => $type,
                    'status' => $result['status'] ?? 'completed',
                    'jumlah_ditarik' => $result['pulled'] ?? 0,
                    'jumlah_konflik' => $result['conflicts'] ?? 0,
                    'jumlah_diperbarui' => $result['updated'] ?? 0,
                    'detail' => $result,
                    'mulai_pada' => now()->subSeconds(microtime(true) - $startTime),
                    'selesai_pada' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::error("SyncOrchestrator: Source '{$src}' failed: {$e->getMessage()}");
                $results[$src] = ['status' => 'failed', 'error' => $e->getMessage()];
            }
        }

        return [
            'status' => 'completed',
            'results' => $results,
            'duration_sec' => round(microtime(true) - $startTime, 2),
        ];
    }

    public function getStatus(): array
    {
        return IntegrasiLogSinkron::select('sumber', 'status', 'selesai_pada')
            ->orderBy('selesai_pada', 'desc')
            ->get()
            ->groupBy('sumber')
            ->map(fn($items) => $items->first())
            ->toArray();
    }

    public function getHistory(int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return IntegrasiLogSinkron::orderBy('mulai_pada', 'desc')->paginate($perPage);
    }
}
