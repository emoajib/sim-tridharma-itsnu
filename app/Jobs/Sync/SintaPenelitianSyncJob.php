<?php

namespace App\Jobs\Sync;

use App\Models\Dosen;
use App\Services\Sync\SintaSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SintaPenelitianSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 3;

    public function __construct(
        public readonly int $dosenId,
        public readonly bool $dryRun = false,
    ) {}

    public function handle(SintaSyncService $syncService): void
    {
        $dosen = Dosen::find($this->dosenId);
        if (!$dosen || !$dosen->sinta_id) {
            Log::warning("SintaPenelitianSyncJob: Dosen #{$this->dosenId} not found or no sinta_id");
            return;
        }

        $result = $syncService->sync('penelitian', $this->dryRun);
        Log::info("SintaPenelitianSyncJob: Dosen {$dosen->nidn} synced", $result);
    }

    public function failed(\Throwable $e): void
    {
        Log::error("SintaPenelitianSyncJob failed for dosen {$this->dosenId}: {$e->getMessage()}");
    }
}
