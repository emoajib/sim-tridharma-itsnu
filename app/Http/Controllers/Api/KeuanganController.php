<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keuangan;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KeuanganController extends Controller
{
    public function index(Request $request)
    {
        $keuangan = Keuangan::with(['dosen', 'prodi', 'periode'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('dosen', function ($q) use ($search) {
                    $q->where('nama_depan', 'like', "%{$search}%");
                });
            })
            ->paginate(10);

        return Inertia::render('Keuangan/Index', [
            'keuangan' => $keuangan,
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
            'periode_id' => 'nullable|exists:m_periode_akademik,id',
            'jenis_dana' => 'required|string|max:50',
            'sumber_dana' => 'nullable|string|max:100',
            'jumlah' => 'required|numeric|min:0',
            'tahun' => 'nullable|string|max:10',
            'keterangan' => 'nullable|string',
            'status' => 'nullable|string|max:30',
        ]);

        Keuangan::create($validated);

        return redirect()->back()->with('success', 'Keuangan berhasil ditambahkan.');
    }

    public function update(Request $request, Keuangan $keuangan)
    {
        $validated = $request->validate([
            'dosen_id' => 'required|exists:m_dosen,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'nullable|exists:m_periode_akademik,id',
            'jenis_dana' => 'required|string|max:50',
            'sumber_dana' => 'nullable|string|max:100',
            'jumlah' => 'required|numeric|min:0',
            'tahun' => 'nullable|string|max:10',
            'keterangan' => 'nullable|string',
            'status' => 'nullable|string|max:30',
        ]);

        $keuangan->update($validated);

        return redirect()->back()->with('success', 'Keuangan berhasil diperbarui.');
    }

    public function destroy(Keuangan $keuangan)
    {
        $keuangan->delete();

        return redirect()->back()->with('success', 'Keuangan berhasil dihapus.');
    }
}
