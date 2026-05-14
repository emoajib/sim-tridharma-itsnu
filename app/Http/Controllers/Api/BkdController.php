<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bkd;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\PeriodeAkademik;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dosen_id' => 'required|exists:m_dosen,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'total_sks' => 'required|numeric|min:0',
        ]);

        Bkd::create($validated);

        return redirect()->back()->with('success', 'BKD berhasil ditambahkan.');
    }

    public function update(Request $request, Bkd $bkd)
    {
        $validated = $request->validate([
            'dosen_id' => 'required|exists:m_dosen,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'total_sks' => 'required|numeric|min:0',
        ]);

        $bkd->update($validated);

        return redirect()->back()->with('success', 'BKD berhasil diperbarui.');
    }

    public function destroy(Bkd $bkd)
    {
        $bkd->delete();

        return redirect()->back()->with('success', 'BKD berhasil dihapus.');
    }
}
