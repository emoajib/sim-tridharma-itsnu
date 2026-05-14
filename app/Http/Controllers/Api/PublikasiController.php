<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Publikasi;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublikasiController extends Controller
{
    public function index(Request $request)
    {
        $publikasi = Publikasi::with(['dosen', 'prodi', 'periode'])
            ->when($request->search, function ($query, $search) {
                $query->where('judul_publikasi', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('Portofolio/Publikasi/Index', [
            'publikasi' => $publikasi,
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
            'judul_publikasi' => 'required',
            'jenis_publikasi' => 'required',
            'tingkat' => 'required',
        ]);

        Publikasi::create($validated);

        return redirect()->back()->with('success', 'Publikasi berhasil ditambahkan.');
    }

    public function update(Request $request, Publikasi $publikasi)
    {
        $validated = $request->validate([
            'dosen_id' => 'required|exists:m_dosen,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'judul_publikasi' => 'required',
            'jenis_publikasi' => 'required',
            'tingkat' => 'required',
        ]);

        $publikasi->update($validated);

        return redirect()->back()->with('success', 'Publikasi berhasil diperbarui.');
    }

    public function destroy(Publikasi $publikasi)
    {
        $publikasi->delete();

        return redirect()->back()->with('success', 'Publikasi berhasil dihapus.');
    }
}
