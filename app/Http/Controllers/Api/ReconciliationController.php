<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReconciliationController extends Controller
{
    public function index(Request $request)
    {
        return inertia('Reconciliation/Index', [
            'reconciliations' => [],
            'stats' => ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0],
        ]);
    }

    public function show(int $id)
    {
        return inertia('Reconciliation/Show', ['reconciliation' => null]);
    }

    public function stats()
    {
        return response()->json([
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
        ]);
    }

    public function approve(int $id)
    {
        return redirect()->route('reconciliation.index')->with('success', 'Data berhasil disetujui.');
    }

    public function batchApprove(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        return redirect()->route('reconciliation.index')->with('success', count($request->ids) . ' data berhasil disetujui.');
    }

    public function reject(Request $request, int $id)
    {
        return redirect()->route('reconciliation.index')->with('success', 'Data berhasil ditolak.');
    }
}
