<?php

namespace App\Console\Commands;

use App\Models\Dosen;
use App\Models\Prodi;
use App\Services\Sync\SyncOrchestrator;
use Illuminate\Console\Command;

class SyncRunCommand extends Command
{
    protected $signature = 'sync:run
        {--source=all : Sumber data: pddikti, sinta, scholar, all}
        {--type=all : Tipe data: dosen, publikasi, penelitian, pkm, all}
        {--dry-run : Mode dry-run tanpa perubahan data}
        {--queue : Dispatch sebagai job antrian}';

    protected $description = 'Run data synchronization from all sources';

    public function handle(SyncOrchestrator $orchestrator): int
    {
        $source = $this->option('source');
        $type = $this->option('type');
        $dryRun = (bool) $this->option('dry-run');

        if ($this->option('queue')) {
            return $this->dispatchJobs($source, $type, $dryRun) ? self::SUCCESS : self::FAILURE;
        }

        $this->info("Starting sync: source={$source}, type={$type}" . ($dryRun ? ' [DRY-RUN]' : ''));

        $start = microtime(true);
        $result = $orchestrator->run($source, $type, $dryRun);
        $duration = round(microtime(true) - $start, 2);

        $this->newLine();
        $this->table(
            ['Source', 'Pulled', 'Updated', 'Conflicts', 'Status'],
            collect($result['results'])->map(fn($r, $src) => [
                $src,
                $r['pulled'] ?? 0,
                $r['updated'] ?? 0,
                $r['conflicts'] ?? 0,
                $r['status'] ?? '?',
            ])->toArray()
        );

        $this->info("Sync completed in {$duration}s.");

        return self::SUCCESS;
    }

    private function dispatchJobs(string $source, string $type, bool $dryRun): bool
    {
        $dispatched = 0;

        if (in_array($source, ['all', 'pddikti'])) {
            $count = Prodi::where('is_active', true)->count();
            Prodi::where('is_active', true)->each(function ($prodi) use ($dryRun) {
                \App\Jobs\Sync\PddiktiDosenSyncJob::dispatch($prodi->id, $dryRun);
            });
            $dispatched += $count;
            $this->info("Dispatched {$count} PddiktiDosenSyncJob(s)");
        }

        if (in_array($source, ['all', 'sinta'])) {
            if (in_array($type, ['all', 'publikasi'])) {
                $count = Dosen::whereNotNull('sinta_id')->count();
                Dosen::whereNotNull('sinta_id')->each(function ($dosen) use ($dryRun) {
                    \App\Jobs\Sync\SintaPublikasiSyncJob::dispatch($dosen->id, $dryRun);
                });
                $dispatched += $count;
                $this->info("Dispatched {$count} SintaPublikasiSyncJob(s)");
            }

            if (in_array($type, ['all', 'penelitian'])) {
                $count = Dosen::whereNotNull('sinta_id')->count();
                Dosen::whereNotNull('sinta_id')->each(function ($dosen) use ($dryRun) {
                    \App\Jobs\Sync\SintaPenelitianSyncJob::dispatch($dosen->id, $dryRun);
                });
                $dispatched += $count;
                $this->info("Dispatched {$count} SintaPenelitianSyncJob(s)");
            }
        }

        if ($dispatched === 0) {
            $this->warn('No jobs dispatched. Check --source and --type parameters.');
            return false;
        }

        return true;
    }
}
