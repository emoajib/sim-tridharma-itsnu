<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentPeringatanLog;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PeringatanController extends Controller
{
    public function index(Request $request)
    {
        $peringatan = AgentPeringatanLog::with(['prodi', 'dosen'])
            ->when($request->prodi_id, fn($q) => $q->where('prodi_id', $request->prodi_id))
            ->when($request->tingkat, fn($q) => $q->where('tingkat', $request->tingkat))
            ->when($request->search, fn($q) => $q->where('pesan', 'like', '%' . $request->search . '%'))
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
            'read_at' => now(),
        ]);

        return back()->with('success', 'Peringatan ditandai sudah dibaca');
    }

    public function markAllAsRead()
    {
        AgentPeringatanLog::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()->with('success', 'Semua peringatan ditandai sudah dibaca');
    }

    public function runAgent(Request $request)
    {
        $request->validate([
            'prodi_id' => 'nullable|integer',
        ]);

        $prodiId = $request->prodi_id ?: null;
        
        \App\Jobs\AgentDispatchJob::dispatch('peringatan', 'run', [
            'prodi_id' => $prodiId,
        ]);

        return back()->with('success', 'Agent Peringatan sedang dijalankan...');
    }

}
