<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KeuanganRequest;
use App\Models\Dosen;
use App\Models\Keuangan;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KeuanganController extends Controller
{
    public function index(Request $request)
    {
        $keuangan = Keuangan::with(['dosen', 'prodi'])
            ->when($request->search, function ($query, $search) {
                $query->where('jenis_dana', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('Keuangan/Index', [
            'keuangan' => $keuangan,
            'dosen_list' => Dosen::select('id', 'nama_depan', 'nidn')->get(),
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
        ]);
    }

    public function store(KeuanganRequest $request)
    {
        Keuangan::create($request->validated());

        return redirect()->back()->with('success', 'Data keuangan berhasil ditambahkan.');
    }

    public function update(KeuanganRequest $request, Keuangan $keuangan)
    {
        $keuangan->update($request->validated());

        return redirect()->back()->with('success', 'Data keuangan berhasil diperbarui.');
    }

    public function destroy(Keuangan $keuangan)
    {
        $keuangan->delete();

        return redirect()->back()->with('success', 'Data keuangan berhasil dihapus.');
    }
}
