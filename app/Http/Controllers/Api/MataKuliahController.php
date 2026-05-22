<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MataKuliahRequest;
use App\Models\MataKuliah;
use App\Models\Prodi;
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
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
        ]);
    }

    public function store(MataKuliahRequest $request)
    {
        MataKuliah::create($request->validated());

        return redirect()->back()->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function update(MataKuliahRequest $request, MataKuliah $mataKuliah)
    {
        $mataKuliah->update($request->validated());

        return redirect()->back()->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(MataKuliah $mataKuliah)
    {
        $mataKuliah->delete();

        return redirect()->back()->with('success', 'Mata kuliah berhasil dihapus.');
    }
}
