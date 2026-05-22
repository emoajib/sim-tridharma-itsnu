<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MahasiswaBimbinganRequest;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\MahasiswaBimbingan;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MahasiswaBimbinganController extends Controller
{
    public function index(Request $request)
    {
        $bimbingan = MahasiswaBimbingan::with(['dosen', 'mahasiswa', 'prodi', 'periode'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('dosen', function ($q) use ($search) {
                    $q->where('nama_depan', 'like', "%{$search}%");
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

    public function store(MahasiswaBimbinganRequest $request)
    {
        MahasiswaBimbingan::create($request->validated());

        return redirect()->back()->with('success', 'Bimbingan berhasil ditambahkan.');
    }

    public function update(MahasiswaBimbinganRequest $request, MahasiswaBimbingan $mahasiswaBimbingan)
    {
        $mahasiswaBimbingan->update($request->validated());

        return redirect()->back()->with('success', 'Bimbingan berhasil diperbarui.');
    }

    public function destroy(MahasiswaBimbingan $mahasiswaBimbingan)
    {
        $mahasiswaBimbingan->delete();

        return redirect()->back()->with('success', 'Bimbingan berhasil dihapus.');
    }
}
