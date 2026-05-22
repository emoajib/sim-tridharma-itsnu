<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PkmRequest;
use App\Models\Dosen;
use App\Models\PeriodeAkademik;
use App\Models\Pkm;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PkmController extends Controller
{
    public function index(Request $request)
    {
        $pkm = Pkm::with(['dosen', 'prodi', 'periode'])
            ->when($request->search, function ($query, $search) {
                $query->where('judul_pkm', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('Portofolio/Pkm/Index', [
            'pkm' => $pkm,
            'dosen_list' => Dosen::select('id', 'nama_depan', 'nama_belakang')->get(),
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
        ]);
    }

    public function store(PkmRequest $request)
    {
        Pkm::create($request->validated());

        return redirect()->back()->with('success', 'PKM berhasil ditambahkan.');
    }

    public function update(PkmRequest $request, Pkm $pkm)
    {
        $pkm->update($request->validated());

        return redirect()->back()->with('success', 'PKM berhasil diperbarui.');
    }

    public function destroy(Pkm $pkm)
    {
        $pkm->delete();

        return redirect()->back()->with('success', 'PKM berhasil dihapus.');
    }
}
