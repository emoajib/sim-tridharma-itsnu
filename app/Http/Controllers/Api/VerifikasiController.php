<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentVerifikasiHasil;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Services\MCP\MCPClientService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VerifikasiController extends Controller
{
    public function index(Request $request)
    {
        $verifikasi = AgentVerifikasiHasil::with(['prodi', 'dosen', 'dokumen'])
            ->when($request->prodi_id, fn ($q) => $q->where('prodi_id', $request->prodi_id))
            ->when($request->dosen_id, fn ($q) => $q->where('dosen_id', $request->dosen_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20);

        $prodi_list = Prodi::select('id', 'nama_prodi')->get();
        $dosen_list = Dosen::select('id', 'nama_depan', 'nama_belakang')->get();

        $stats = [
            'total' => AgentVerifikasiHasil::count(),
            'valid' => AgentVerifikasiHasil::where('status', 'valid')->count(),
            'need_review' => AgentVerifikasiHasil::where('status', 'need_review')->count(),
            'invalid' => AgentVerifikasiHasil::where('status', 'invalid')->count(),
        ];

        return Inertia::render('Verifikasi/Index', [
            'verifikasi' => $verifikasi,
            'prodi_list' => $prodi_list,
            'dosen_list' => $dosen_list,
            'stats' => $stats,
            'filters' => [
                'prodi_id' => $request->prodi_id,
                'dosen_id' => $request->dosen_id,
                'status' => $request->status,
            ],
        ]);
    }

    public function runAgent(Request $request)
    {
        $request->validate([
            'prodi_id' => 'nullable|integer',
            'dosen_id' => 'nullable|integer',
        ]);

        $mcpClient = app(MCPClientService::class);

        try {
            $result = $mcpClient->runVerifikasiDokumen(
                $request->prodi_id ?? 0,
                $request->dosen_id
            );

            return back()->with('success', "Agent Verifikasi selesai: {$result['valid_count']} valid, {$result['need_review_count']} perlu review");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menjalankan agent: '.$e->getMessage());
        }
    }
}
