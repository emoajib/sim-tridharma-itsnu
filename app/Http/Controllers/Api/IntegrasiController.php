<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IntegrasiLogSinkron;
use App\Models\Prodi;
use App\Services\MCP\MCPClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IntegrasiController extends Controller
{
    public function index(Request $request)
    {
        $prodi_list = Prodi::select('id', 'nama_prodi', 'kode_prodi')->get();

        $logs = IntegrasiLogSinkron::when($request->sumber, fn ($q) => $q->where('sumber', $request->sumber))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('mulai_pada', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Agent/Integrasi/Index', [
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

    public function sync(Request $request): RedirectResponse
    {
        return $this->run($request);
    }

    public function syncAll(Request $request): RedirectResponse
    {
        $sources = $request->input('sources', ['pddikti', 'sinta']);
        if (is_string($sources)) {
            $sources = explode(',', $sources);
        }

        $totalPulled = 0;
        $totalErrors = 0;
        $errors = [];

        foreach ($sources as $source) {
            try {
                $mcpClient = app(MCPClientService::class);
                $result = $mcpClient->runIntegrasiSync($source);
                $totalPulled += $result['records_pulled'] ?? 0;
            } catch (\Exception $e) {
                $totalErrors++;
                $errors[] = "{$source}: {$e->getMessage()}";
            }
        }

        $message = "Sinkronisasi selesai: {$totalPulled} records ditarik";
        if ($totalErrors > 0) {
            $message .= ", {$totalErrors} error: " . implode('; ', $errors);
            return back()->with('error', $message);
        }

        return back()->with('success', $message);
    }
}
