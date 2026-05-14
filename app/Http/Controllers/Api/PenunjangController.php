<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penunjang;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\PeriodeAkademik;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dosen_id' => 'required|exists:m_dosen,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'nama_kegiatan' => 'required',
            'jenis_kegiatan' => 'required',
        ]);

        Penunjang::create($validated);

        return redirect()->back()->with('success', 'Penunjang berhasil ditambahkan.');
    }

    public function update(Request $request, Penunjang $penunjang)
    {
        $validated = $request->validate([
            'dosen_id' => 'required|exists:m_dosen,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'nama_kegiatan' => 'required',
            'jenis_kegiatan' => 'required',
        ]);

        $penunjang->update($validated);

        return redirect()->back()->with('success', 'Penunjang berhasil diperbarui.');
    }

    public function destroy(Penunjang $penunjang)
    {
        $penunjang->delete();

        return redirect()->back()->with('success', 'Penunjang berhasil dihapus.');
    }
}
