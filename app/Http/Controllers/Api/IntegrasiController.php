<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IntegrasiLogSinkron;
use App\Models\Prodi;
use App\Services\MCP\MCPClientService;
use Illuminate\Http\Request;

class IntegrasiController extends Controller
{
    public function index(Request $request)
    {
        $prodi_list = Prodi::select('id', 'nama_prodi', 'kode_prodi')->get();

        $logs = IntegrasiLogSinkron::when($request->sumber, fn ($q) => $q->where('sumber', $request->sumber))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('waktu_mulai', 'desc')
            ->paginate(20)
            ->withQueryString();

        return inertia('Agent/Integrasi/Index', [
            'logs' => $logs,
            'prodi_list' => $prodi_list,
            'filters' => [
                'sumber' => $request->sumber,
                'status' => $request->status,
            ],
        ]);
    }

    public function run(Request $request)
    {
        $request->validate([
            'sumber' => 'nullable|in:pddikti,sinta,sister',
        ]);

        $sumber = $request->sumber ?? 'pddikti';

        $mcpClient = app(MCPClientService::class);

        try {
            $result = $mcpClient->runIntegrasiSync($sumber);

            return back()->with('success', "Integrasi {$sumber} selesai: {$result['records_pulled']} records, {$result['conflicts_detected']} conflicts");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menjalankan integrasi: '.$e->getMessage());
        }
    }

    public function sync(Request $request)
    {
        $sumber = $request->get('sumber', 'pddikti');

        $mcpClient = app(MCPClientService::class);

        try {
            $result = $mcpClient->runIntegrasiSync($sumber);

            return back()->with('success', "Sinkronisasi {$sumber} selesai: {$result['records_pulled']} records");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal sinkronisasi: '.$e->getMessage());
        }
    }
}
