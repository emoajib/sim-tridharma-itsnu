<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentGeneratorHistory;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Services\MCP\MCPClientService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GeneratorController extends Controller
{
    public function index(Request $request)
    {
        $prodi_list = Prodi::select('id', 'nama_prodi')->get();
        $periode_list = PeriodeAkademik::select('id', 'nama_periode')->orderByDesc('tanggal_mulai')->get();

        $history = AgentGeneratorHistory::with(['prodi', 'periode'])
            ->when($request->prodi_id, fn ($q) => $q->where('prodi_id', $request->prodi_id))
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('Generator/Index', [
            'prodi_list' => $prodi_list,
            'periode_list' => $periode_list,
            'history' => $history,
            'filters' => [
                'prodi_id' => $request->prodi_id,
            ],
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'prodi_id' => 'required|integer',
            'periode_id' => 'required|integer',
            'jenis_dokumen' => 'required|in:led,lkpt',
        ]);

        $mcpClient = app(MCPClientService::class);

        try {
            $result = $mcpClient->runGeneratorDokumen(
                $request->prodi_id,
                $request->periode_id,
                strtoupper($request->jenis_dokumen)
            );

            if (isset($result['error'])) {
                return back()->with('error', $result['error']);
            }

            // Extract filename from file path for display
            $filename = isset($result['file_path']) ? basename($result['file_path']) : 'Document';

            return back()->with('success', "Dokumen {$result['jenis_dokumen']} berhasil dibuat: {$filename}");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat dokumen: '.$e->getMessage());
        }
    }

    public function download($id)
    {
        $record = AgentGeneratorHistory::findOrFail($id);

        if (! $record->file_path || ! file_exists($record->file_path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return response()->download($record->file_path, $record->judul.'.docx');
    }
}
