<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KegiatanPendidikan;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\PeriodeAkademik;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KegiatanPendidikanController extends Controller
{
    public function index(Request $request)
    {
        $pendidikan = KegiatanPendidikan::with(['dosen', 'prodi', 'periode', 'mataKuliah'])
            ->when($request->search, function ($query, $search) {
                $query->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhereHas('dosen', function ($q) use ($search) {
                        $q->where('nama_depan', 'like', "%{$search}%");
                    });
            })
            ->paginate(10);

        return Inertia::render('Portofolio/KegiatanPendidikan/Index', [
            'kegiatan_pendidikan' => $pendidikan,
            'dosen_list' => Dosen::select('id', 'nama_depan', 'nama_belakang')->get(),
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
            'mata_kuliah_list' => MataKuliah::select('id', 'kode_mk', 'nama_mk')->get(),
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

        KegiatanPendidikan::create($validated);

        return redirect()->back()->with('success', 'Kegiatan pendidikan berhasil ditambahkan.');
    }

    public function update(Request $request, KegiatanPendidikan $kegiatanPendidikan)
    {
        $validated = $request->validate([
            'dosen_id' => 'required|exists:m_dosen,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'nama_kegiatan' => 'required',
            'jenis_kegiatan' => 'required',
        ]);

        $kegiatanPendidikan->update($validated);

        return redirect()->back()->with('success', 'Kegiatan pendidikan berhasil diperbarui.');
    }

    public function destroy(KegiatanPendidikan $kegiatanPendidikan)
    {
        $kegiatanPendidikan->delete();

        return redirect()->back()->with('success', 'Kegiatan pendidikan berhasil dihapus.');
    }
}
