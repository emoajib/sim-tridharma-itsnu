<?php

namespace App\Console\Commands;

use App\Services\MCP\MCPClientService;
use Illuminate\Console\Command;

class AgentsRunAll extends Command
{
    protected $signature = 'agents:run-all {--agent= : Specific agent to run}';

    protected $description = 'Run all AI agents or a specific one via MCP';

    public function handle(): void
    {
        $agents = $this->option('agent')
            ? [$this->option('agent')]
            : ['verifikasi', 'prediksi', 'rekomendasi', 'peringatan', 'generator', 'integrasi'];

        $mcp = app(MCPClientService::class);

        $toolMap = [
            'verifikasi' => 'verifikasi_dokumen',
            'prediksi' => 'prediksi_skor',
            'rekomendasi' => 'rekomendasi_generate',
            'peringatan' => 'peringatan_check',
            'generator' => 'generator_dokumen',
            'integrasi' => 'integrasi_sync',
        ];

        foreach ($agents as $agent) {
            $toolName = $toolMap[$agent] ?? null;
            if (!$toolName) {
                $this->warn("Unknown agent: {$agent}");
                continue;
            }

            try {
                $result = $mcp->callTool($toolName, []);
                $this->info("Agent {$agent} executed successfully");
            } catch (\Exception $e) {
                $this->error("Agent {$agent} failed: {$e->getMessage()}");
            }
        }

        $this->info('All agents processed.');
    }
}
