<?php

namespace App\Console\Commands;

use App\Models\Dosen;
use App\Models\ReconciliationSuggestion;
use Illuminate\Console\Command;

class AutoReconcile extends Command
{
    protected $signature = 'reconcile:auto {--dry-run : Simulate without making changes} {--hours=24 : Auto-approve suggestions older than N hours}';

    protected $description = 'Auto-approve high-confidence reconciliation suggestions';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Auto-reconcile: scanning for suggestions with score >= 95 older than {$hours} hours...");

        $suggestions = ReconciliationSuggestion::where('status', 'pending')
            ->where('similarity_score', '>=', 95)
            ->where('created_at', '<', now()->subHours($hours))
            ->get();

        if ($suggestions->isEmpty()) {
            $this->info('No suggestions to auto-approve.');
            return Command::SUCCESS;
        }

        $this->info("Found {$suggestions->count()} suggestion(s) to process.");

        $approved = 0;
        $skipped = 0;

        foreach ($suggestions as $s) {
            $this->line("  [{$s->id}] {$s->source_type}: score={$s->similarity_score}");

            if ($dryRun) {
                $this->warn("    → Would approve (dry-run)");
                $approved++;
                continue;
            }

            if ($s->target_table === 'm_dosen' && $s->target_id) {
                $dosen = Dosen::find($s->target_id);
                if ($dosen) {
                    $sourceData = $s->source_data;
                    $dosen->update([
                        'nama_depan' => $sourceData['nama_depan'] ?? $sourceData['nama'] ?? $dosen->nama_depan,
                        'nama_belakang' => $sourceData['nama_belakang'] ?? $dosen->nama_belakang,
                    ]);
                }
            }

            $s->update([
                'status' => 'auto_approved',
                'reviewed_at' => now(),
                'notes' => 'Auto-approved by system (score >= 95, age > ' . $hours . ' hours)',
            ]);

            $this->info("    → Approved");
            $approved++;
        }

        $this->newLine();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total found', $suggestions->count()],
                ['Approved', $approved],
                ['Skipped', $skipped],
                ['Mode', $dryRun ? 'Dry-run' : 'Live'],
            ]
        );

        return Command::SUCCESS;
    }
}
