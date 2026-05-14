<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProdiController extends Controller
{
    public function index(Request $request)
    {
        $prodi = Prodi::with('fakultas')
            ->when($request->search, function ($query, $search) {
                $query->where('kode_prodi', 'like', "%{$search}%")
                    ->orWhere('nama_prodi', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('MasterData/Prodi/Index', [
            'prodi' => $prodi,
            'fakultas_list' => \App\Models\Fakultas::select('id', 'nama_fakultas')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_prodi' => 'required|string|unique:m_prodi,kode_prodi',
            'nama_prodi' => 'required|string',
            'fakultas_id' => 'required|exists:m_fakultas,id',
            'jenjang' => 'required|string',
        ]);

        Prodi::create($validated);

        return redirect()->back()->with('success', 'Prodi berhasil ditambahkan.');
    }

    public function update(Request $request, Prodi $prodi)
    {
        $validated = $request->validate([
            'kode_prodi' => 'required|string|unique:m_prodi,kode_prodi,' . $prodi->id,
            'nama_prodi' => 'required|string',
            'fakultas_id' => 'required|exists:m_fakultas,id',
            'jenjang' => 'required|string',
        ]);

        $prodi->update($validated);

        return redirect()->back()->with('success', 'Prodi berhasil diperbarui.');
    }

    public function destroy(Prodi $prodi)
    {
        $prodi->delete();

        return redirect()->back()->with('success', 'Prodi berhasil dihapus.');
    }
}
