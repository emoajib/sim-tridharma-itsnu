<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenunjangRequest;
use App\Models\Dosen;
use App\Models\Penunjang;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PenunjangController extends Controller
{
    public function index(Request $request)
    {
        $penunjang = Penunjang::with(['dosen', 'prodi', 'periode'])
            ->when($request->search, function ($query, $search) {
                $query->where('nama_kegiatan', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('Portofolio/Penunjang/Index', [
            'penunjang' => $penunjang,
            'dosen_list' => Dosen::select('id', 'nama_depan', 'nama_belakang')->get(),
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
        ]);
    }

    public function store(PenunjangRequest $request)
    {
        Penunjang::create($request->validated());

        return redirect()->back()->with('success', 'Kegiatan penunjang berhasil ditambahkan.');
    }

    public function update(PenunjangRequest $request, Penunjang $penunjang)
    {
        $penunjang->update($request->validated());

        return redirect()->back()->with('success', 'Kegiatan penunjang berhasil diperbarui.');
    }

    public function destroy(Penunjang $penunjang)
    {
        $penunjang->delete();

        return redirect()->back()->with('success', 'Kegiatan penunjang berhasil dihapus.');
    }
}
