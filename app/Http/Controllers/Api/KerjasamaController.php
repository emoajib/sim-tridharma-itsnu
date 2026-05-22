<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KerjasamaRequest;
use App\Models\Kerjasama;
use App\Models\Mitra;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KerjasamaController extends Controller
{
    public function index(Request $request)
    {
        $kerjasama = Kerjasama::with(['mitra', 'prodi'])
            ->when($request->search, function ($query, $search) {
                $query->where('nomor_mou', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('Kerjasama/Index', [
            'kerjasama' => $kerjasama,
            'mitra_list' => Mitra::select('id', 'nama_mitra')->get(),
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
        ]);
    }

    public function store(KerjasamaRequest $request)
    {
        Kerjasama::create($request->validated());

        return redirect()->back()->with('success', 'Kerjasama berhasil ditambahkan.');
    }

    public function update(KerjasamaRequest $request, Kerjasama $kerjasama)
    {
        $kerjasama->update($request->validated());

        return redirect()->back()->with('success', 'Kerjasama berhasil diperbarui.');
    }

    public function destroy(Kerjasama $kerjasama)
    {
        $kerjasama->delete();

        return redirect()->back()->with('success', 'Kerjasama berhasil dihapus.');
    }
}
