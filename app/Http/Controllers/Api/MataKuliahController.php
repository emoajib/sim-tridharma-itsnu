<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MataKuliahController extends Controller
{
    public function index(Request $request)
    {
        $mataKuliah = MataKuliah::with('prodi')
            ->when($request->search, function ($query, $search) {
                $query->where('kode_mk', 'like', "%{$search}%")
                    ->orWhere('nama_mk', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('MasterData/MataKuliah/Index', [
            'mataKuliah' => $mataKuliah,
            'prodi_list' => \App\Models\Prodi::select('id', 'nama_prodi')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_mk' => 'required|string|unique:m_mata_kuliah,kode_mk',
            'nama_mk' => 'required|string',
            'prodi_id' => 'required|exists:m_prodi,id',
        ]);

        MataKuliah::create($validated);

        return redirect()->back()->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function update(Request $request, MataKuliah $mataKuliah)
    {
        $validated = $request->validate([
            'kode_mk' => 'required|string|unique:m_mata_kuliah,kode_mk,' . $mataKuliah->id,
            'nama_mk' => 'required|string',
            'prodi_id' => 'required|exists:m_prodi,id',
        ]);

        $mataKuliah->update($validated);

        return redirect()->back()->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(MataKuliah $mataKuliah)
    {
        $mataKuliah->delete();

        return redirect()->back()->with('success', 'Mata kuliah berhasil dihapus.');
    }
}
