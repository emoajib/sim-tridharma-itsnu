<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rps;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RpsController extends Controller
{
    public function index(Request $request)
    {
        $rps = Rps::with('mataKuliah', 'prodi', 'periode')
            ->when($request->search, function ($q, $s) {
                $q->whereHas('mataKuliah', fn($q) => $q->where('nama_mk', 'like', "%{$s}%"))
                  ->orWhere('kode_rps', 'like', "%{$s}%");
            })
            ->paginate(10);

        return Inertia::render('Kurikulum/Rps/Index', [
            'rps' => $rps,
            'mk_list' => MataKuliah::select('id', 'kode_mk', 'nama_mk')->get(),
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mata_kuliah_id' => 'required|exists:m_mata_kuliah,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'nullable|exists:m_periode_akademik,id',
            'kode_rps' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $data = $validated;

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('rps', 'public');
        }

        Rps::create($data);

        return redirect()->back()->with('success', 'RPS berhasil ditambahkan.');
    }

    public function update(Request $request, Rps $rp)
    {
        $validated = $request->validate([
            'mata_kuliah_id' => 'required|exists:m_mata_kuliah,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'nullable|exists:m_periode_akademik,id',
            'kode_rps' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'status' => 'required|string|in:draft,selesai',
        ]);

        $data = $validated;

        if ($request->hasFile('file')) {
            if ($rp->file_path) {
                \Storage::disk('public')->delete($rp->file_path);
            }
            $data['file_path'] = $request->file('file')->store('rps', 'public');
        }

        $rp->update($data);

        return redirect()->back()->with('success', 'RPS berhasil diperbarui.');
    }

    public function destroy(Rps $rp)
    {
        if ($rp->file_path) {
            \Storage::disk('public')->delete($rp->file_path);
        }
        $rp->delete();
        return redirect()->back()->with('success', 'RPS berhasil dihapus.');
    }
}
