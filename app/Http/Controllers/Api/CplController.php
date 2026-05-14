<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cpl;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CplController extends Controller
{
    public function index(Request $request)
    {
        $cpl = Cpl::with('prodi')
            ->when($request->search, function ($query, $search) {
                $query->where('kode_cpl', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('MasterData/Cpl/Index', [
            'cpl' => $cpl,
            'prodi_list' => \App\Models\Prodi::select('id', 'nama_prodi')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_cpl' => 'required|string',
            'prodi_id' => 'required|exists:m_prodi,id',
            'deskripsi' => 'required|string',
        ]);

        Cpl::create($validated);

        return redirect()->back()->with('success', 'CPL berhasil ditambahkan.');
    }

    public function update(Request $request, Cpl $cpl)
    {
        $validated = $request->validate([
            'kode_cpl' => 'required|string',
            'prodi_id' => 'required|exists:m_prodi,id',
            'deskripsi' => 'required|string',
        ]);

        $cpl->update($validated);

        return redirect()->back()->with('success', 'CPL berhasil diperbarui.');
    }

    public function destroy(Cpl $cpl)
    {
        $cpl->delete();

        return redirect()->back()->with('success', 'CPL berhasil dihapus.');
    }
}
