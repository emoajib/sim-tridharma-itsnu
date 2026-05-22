<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentPeringatanLog;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\User;
use App\Services\MCP\MCPClientService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PeringatanController extends Controller
{
    public function index(Request $request)
    {
        $peringatan = AgentPeringatanLog::with(['prodi', 'dosen'])
            ->when($request->prodi_id, fn ($q) => $q->where('prodi_id', $request->prodi_id))
            ->when($request->tingkat, fn ($q) => $q->where('tingkat', $request->tingkat))
            ->when($request->search, fn ($q) => $q->where('pesan', 'like', '%'.$request->search.'%'))
            ->orderByDesc('created_at')
            ->paginate(20);

        $prodi_list = Prodi::select('id', 'nama_prodi')->get();
        $stats = [
            'total' => AgentPeringatanLog::count(),
            'critical' => AgentPeringatanLog::where('tingkat', 'critical')->count(),
            'warning' => AgentPeringatanLog::where('tingkat', 'warning')->count(),
            'info' => AgentPeringatanLog::where('tingkat', 'info')->count(),
            'unread' => AgentPeringatanLog::where('is_read', false)->count(),
        ];

        return Inertia::render('Peringatan/Index', [
            'peringatan' => $peringatan,
            'prodi_list' => $prodi_list,
            'stats' => $stats,
            'filters' => [
                'prodi_id' => $request->prodi_id,
                'tingkat' => $request->tingkat,
                'search' => $request->search,
            ],
        ]);
    }

    public function markAsRead($id)
    {
        $peringatan = AgentPeringatanLog::findOrFail($id);
        $peringatan->update([
            'is_read' => true,
            'dibaca_pada' => now(),
        ]);

        return back()->with('success', 'Peringatan ditandai sudah dibaca');
    }

    public function markAllAsRead()
    {
        $query = AgentPeringatanLog::where('is_read', false);

        /** @var User $user */
        $user = auth()->user();

        $prodiId = $user->prodi_id;

        if (! $prodiId && $user->dosen_id) {
            $dosen = Dosen::find($user->dosen_id);
            $prodiId = $dosen?->prodi_id;
        }

        if ($prodiId) {
            $query->where('prodi_id', $prodiId);
        }

        $query->update([
            'is_read' => true,
            'dibaca_pada' => now(),
        ]);

        return back()->with('success', 'Semua peringatan ditandai sudah dibaca');
    }

    public function runAgent(Request $request)
    {
        $request->validate([
            'prodi_id' => 'nullable|integer',
        ]);

        $prodiId = $request->prodi_id ?: null;

        $mcpClient = app(MCPClientService::class);

        try {
            $result = $mcpClient->runPeringatanCheck($prodiId);

            return back()->with('success', "Agent Peringatan selesai: {$result['warning_count']} peringatan ditemukan");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menjalankan agent: '.$e->getMessage());
        }
    }
}
