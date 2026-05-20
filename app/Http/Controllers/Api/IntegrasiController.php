<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IntegrasiLogSinkron;
use App\Models\Prodi;
use Illuminate\Http\Request;

class IntegrasiController extends Controller
{
    public function index(Request $request)
    {
        $prodi_list = Prodi::select('id', 'nama_prodi', 'kode_prodi')->get();

        $logs = IntegrasiLogSinkron::when($request->sumber, fn($q) => $q->where('sumber', $request->sumber))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
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
        \App\Jobs\AgentDispatchJob::dispatch('integrasi', 'run', $request->all());

        return back()->with('success', 'Agent Integrasi sedang diproses.');
    }

    public function sync(Request $request)
    {
        $sumber = $request->get('sumber', 'PDDIKTI');
        \App\Jobs\AgentDispatchJob::dispatch('integrasi', 'sync', ['sumber' => $sumber]);

        return back()->with('success', "Sinkronisasi {$sumber} sedang diproses.");
    }
}