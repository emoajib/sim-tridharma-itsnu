<?php

namespace App\Console\Commands;

use App\Jobs\AgentDispatchJob;
use Illuminate\Console\Command;

class AgentsRunAll extends Command
{
    protected $signature = 'agents:run-all {--agent= : Specific agent to run}';

    protected $description = 'Dispatch all AI agents or a specific one';

    public function handle(): void
    {
        $agents = $this->option('agent')
            ? [$this->option('agent')]
            : ['verifikasi', 'prediksi', 'rekomendasi', 'peringatan', 'generator', 'integrasi'];

        foreach ($agents as $agent) {
            AgentDispatchJob::dispatch($agent, 'run', []);
            $this->info("Dispatched agent: {$agent}");
        }

        $this->info('All agents dispatched to queue.');
    }
}
