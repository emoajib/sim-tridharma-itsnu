<?php

namespace App\Jobs\Sync;

use App\Models\Prodi;
use App\Services\Sync\PddiktiSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PddiktiDosenSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 3;

    public function __construct(
        public readonly int $prodiId,
        public readonly bool $dryRun = false,
    ) {}

    public function handle(PddiktiSyncService $syncService): void
    {
        $prodi = Prodi::find($this->prodiId);
        if (!$prodi) {
            Log::warning("PddiktiDosenSyncJob: Prodi #{$this->prodiId} not found");
            return;
        }

        $result = $syncService->syncDosen($this->dryRun);
        Log::info("PddiktiDosenSyncJob: Prodi {$prodi->kode_prodi} synced", $result);
    }

    public function failed(\Throwable $e): void
    {
        Log::error("PddiktiDosenSyncJob failed for prodi {$this->prodiId}: {$e->getMessage()}");
    }
}
