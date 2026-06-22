<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MCP\MCPClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PeringatanController extends Controller
{
    protected MCPClientService $mcpClient;

    public function __construct(MCPClientService $mcpClient)
    {
        $this->mcpClient = $mcpClient;
    }

    public function index(): Response
    {
        return Inertia::render('Peringatan/Index', [
            'message' => 'Peringatan agent endpoint',
            'status' => 'ready',
        ]);
    }

    public function runAgent(Request $request)
    {
        $request->validate([
            'prodi_id' => 'nullable|integer',
            'periode_id' => 'nullable|integer',
        ]);

        $prodiId = $request->prodi_id ?: null;
        $periodeId = $request->periode_id ?: null;

        try {
            $result = $this->mcpClient->runPeringatanAgent($prodiId, $periodeId);

            if (isset($result['error'])) {
                return back()->with('error', $result['error']);
            }

            $warnings = $result['warnings'] ?? [];
            $count = is_array($warnings) ? count($warnings) : 0;

            return back()->with('success', "Agent Peringatan selesai: {$count} peringatan ditemukan.");
        } catch (\Exception $e) {
            Log::error('Agent peringatan failed: '.$e->getMessage());

            return back()->with('error', 'Gagal menjalankan agent: '.$e->getMessage());
        }
    }

}
