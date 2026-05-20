<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penelitian;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\PeriodeAkademik;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dosen_id' => 'required|exists:m_dosen,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'judul_penelitian' => 'required|string',
            'jenis_penelitian' => 'required|string',
            'sumber_dana' => 'nullable|string',
            'jumlah_dana' => 'nullable|numeric|min:0',
            'tahun_pelaksanaan' => 'required|string|size:4',
        ]);

        Penelitian::create($validated);

        return redirect()->back()->with('success', 'Penelitian berhasil ditambahkan.');
    }

    public function update(Request $request, Penelitian $penelitian)
    {
        $validated = $request->validate([
            'dosen_id' => 'required|exists:m_dosen,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'judul_penelitian' => 'required',
            'jenis_penelitian' => 'required',
        ]);

        $penelitian->update($validated);

        return redirect()->back()->with('success', 'Penelitian berhasil diperbarui.');
    }

    public function destroy(Penelitian $penelitian)
    {
        $penelitian->delete();

        return redirect()->back()->with('success', 'Penelitian berhasil dihapus.');
    }
}
