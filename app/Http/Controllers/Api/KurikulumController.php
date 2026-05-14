<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KurikulumController extends Controller
{
    public function index(Request $request)
    {
        $kurikulum = Kurikulum::with('prodi')
            ->when($request->search, function ($query, $search) {
                $query->where('nama_kurikulum', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('MasterData/Kurikulum/Index', [
            'kurikulum' => $kurikulum,
            'prodi_list' => \App\Models\Prodi::select('id', 'nama_prodi')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kurikulum' => 'required|string',
            'prodi_id' => 'required|exists:m_prodi,id',
            'tahun_berlaku' => 'required|string',
        ]);

        Kurikulum::create($validated);

        return redirect()->back()->with('success', 'Kurikulum berhasil ditambahkan.');
    }

    public function update(Request $request, Kurikulum $kurikulum)
    {
        $validated = $request->validate([
            'nama_kurikulum' => 'required|string',
            'prodi_id' => 'required|exists:m_prodi,id',
            'tahun_berlaku' => 'required|string',
        ]);

        $kurikulum->update($validated);

        return redirect()->back()->with('success', 'Kurikulum berhasil diperbarui.');
    }

    public function destroy(Kurikulum $kurikulum)
    {
        $kurikulum->delete();

        return redirect()->back()->with('success', 'Kurikulum berhasil dihapus.');
    }
}
