<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MahasiswaBimbingan;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MahasiswaBimbinganController extends Controller
{
    public function index(Request $request)
    {
        $bimbingan = MahasiswaBimbingan::with(['dosen', 'mahasiswa', 'prodi', 'periode'])
            ->when($request->search, function ($query, $search) {
                $query->where('judul', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($q) use ($search) {
                        $q->where('nama', 'like', "%{$search}%");
                    });
            })
            ->paginate(10);

        return Inertia::render('Bimbingan/Index', [
            'bimbingan' => $bimbingan,
            'dosen_list' => Dosen::select('id', 'nama_depan', 'nidn')->get(),
            'mahasiswa_list' => Mahasiswa::select('id', 'nim', 'nama')->get(),
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dosen_id' => 'required|exists:m_dosen,id',
            'mahasiswa_id' => 'required|exists:m_mahasiswa,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'jenis_bimbingan' => 'required|string',
        ]);

        MahasiswaBimbingan::create($validated);

        return redirect()->back()->with('success', 'Bimbingan berhasil ditambahkan.');
    }

    public function update(Request $request, MahasiswaBimbingan $mahasiswaBimbingan)
    {
        $validated = $request->validate([
            'dosen_id' => 'required|exists:m_dosen,id',
            'mahasiswa_id' => 'required|exists:m_mahasiswa,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'jenis_bimbingan' => 'required|string',
        ]);

        $mahasiswaBimbingan->update($validated);

        return redirect()->back()->with('success', 'Bimbingan berhasil diperbarui.');
    }

    public function destroy(MahasiswaBimbingan $mahasiswaBimbingan)
    {
        $mahasiswaBimbingan->delete();

        return redirect()->back()->with('success', 'Bimbingan berhasil dihapus.');
    }
}
