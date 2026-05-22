<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublikasiRequest;
use App\Models\Dosen;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\Publikasi;
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

    public function store(PublikasiRequest $request)
    {
        Publikasi::create($request->validated());

        return redirect()->back()->with('success', 'Publikasi berhasil ditambahkan.');
    }

    public function update(PublikasiRequest $request, Publikasi $publikasi)
    {
        $publikasi->update($request->validated());

        return redirect()->back()->with('success', 'Publikasi berhasil diperbarui.');
    }

    public function destroy(Publikasi $publikasi)
    {
        $publikasi->delete();

        return redirect()->back()->with('success', 'Publikasi berhasil dihapus.');
    }
}
