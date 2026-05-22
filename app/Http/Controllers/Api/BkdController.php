<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BkdRequest;
use App\Models\Bkd;
use App\Models\Dosen;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BkdController extends Controller
{
    public function index(Request $request)
    {
        $bkd = Bkd::with(['dosen', 'prodi', 'periode'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('dosen', function ($q) use ($search) {
                    $q->where('nama_depan', 'like', "%{$search}%");
                })->orWhereHas('periode', function ($q) use ($search) {
                    $q->where('nama_periode', 'like', "%{$search}%");
                });
            })
            ->paginate(10);

        return Inertia::render('Bkd/Index', [
            'bkd' => $bkd,
            'dosen_list' => Dosen::select('id', 'nama_depan', 'nidn')->get(),
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
        ]);
    }

    public function store(BkdRequest $request)
    {
        Bkd::create($request->validated());

        return redirect()->back()->with('success', 'BKD berhasil ditambahkan.');
    }

    public function update(BkdRequest $request, Bkd $bkd)
    {
        $bkd->update($request->validated());

        return redirect()->back()->with('success', 'BKD berhasil diperbarui.');
    }

    public function destroy(Bkd $bkd)
    {
        $bkd->delete();

        return redirect()->back()->with('success', 'BKD berhasil dihapus.');
    }
}
