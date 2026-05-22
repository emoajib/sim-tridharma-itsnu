<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenelitianRequest;
use App\Models\Dosen;
use App\Models\Penelitian;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PenelitianController extends Controller
{
    public function index(Request $request)
    {
        $penelitian = Penelitian::with(['dosen', 'prodi', 'periode'])
            ->when($request->search, function ($query, $search) {
                $query->where('judul_penelitian', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('Portofolio/Penelitian/Index', [
            'penelitian' => $penelitian,
            'dosen_list' => Dosen::select('id', 'nama_depan', 'nama_belakang')->get(),
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
        ]);
    }

    public function store(PenelitianRequest $request)
    {
        Penelitian::create($request->validated());

        return redirect()->back()->with('success', 'Penelitian berhasil ditambahkan.');
    }

    public function update(PenelitianRequest $request, Penelitian $penelitian)
    {
        $penelitian->update($request->validated());

        return redirect()->back()->with('success', 'Penelitian berhasil diperbarui.');
    }

    public function destroy(Penelitian $penelitian)
    {
        $penelitian->delete();

        return redirect()->back()->with('success', 'Penelitian berhasil dihapus.');
    }
}
