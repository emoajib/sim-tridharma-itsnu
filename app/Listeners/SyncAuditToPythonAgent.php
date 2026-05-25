<?php

namespace App\Listeners;

use App\Events\AuditStatusChanged;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Log;

class SyncAuditToPythonAgent
{
    /**
     * Dispatch a background call to the Python MCP agent for AI recommendations
     * when an audit with severity >= sedang changes status.
     */
    public function handle(AuditStatusChanged $event): void
    {
        $audit = $event->audit;
        $severityOrder = ['ringan' => 1, 'sedang' => 2, 'berat' => 3, 'kritis' => 4];
        $severityLevel = $severityOrder[$audit->severity ?? 'ringan'] ?? 1;

        // Only sync for severity >= sedang
        if ($severityLevel < 2) {
            return;
        }

        try {
            $mcp = app(MCPClientService::class);

            // Fire-and-forget: dispatch recommendation generation and early warning check
            $mcp->runRekomendasiGenerate($audit->prodi_id);
            $mcp->runPeringatanCheck($audit->prodi_id);

            Log::info("Audit synced to Python agent for AI recommendations", [
                'audit_id' => $audit->id,
                'status' => $event->newStatus,
                'severity' => $audit->severity,
                'prodi_id' => $audit->prodi_id,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to sync audit to Python agent", [
                'audit_id' => $audit->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
