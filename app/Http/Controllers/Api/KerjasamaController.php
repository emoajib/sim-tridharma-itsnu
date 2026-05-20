<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kerjasama;
use App\Models\Mitra;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KerjasamaController extends Controller
{
    public function index(Request $request)
    {
        $kerjasama = Kerjasama::with(['mitra', 'prodi'])
            ->when($request->search, function ($query, $search) {
                $query->where('nomor_mou', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('Kerjasama/Index', [
            'kerjasama' => $kerjasama,
            'mitra_list' => Mitra::select('id', 'nama_mitra')->get(),
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mitra_id' => 'required|exists:m_mitra,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'jenis_kerjasama' => 'required|string|max:50',
            'nomor_mou' => 'required|string|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'nullable|string|max:30',
            'is_active' => 'nullable|boolean',
        ]);

        Kerjasama::create($validated);

        return redirect()->back()->with('success', 'Kerjasama berhasil ditambahkan.');
    }

    public function update(Request $request, Kerjasama $kerjasama)
    {
        $validated = $request->validate([
            'mitra_id' => 'required|exists:m_mitra,id',
            'prodi_id' => 'required|exists:m_prodi,id',
            'jenis_kerjasama' => 'required|string|max:50',
            'nomor_mou' => 'required|string|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
            'status' => 'nullable|string|max:30',
            'is_active' => 'nullable|boolean',
        ]);

        $kerjasama->update($validated);

        return redirect()->back()->with('success', 'Kerjasama berhasil diperbarui.');
    }

    public function destroy(Kerjasama $kerjasama)
    {
        $kerjasama->delete();

        return redirect()->back()->with('success', 'Kerjasama berhasil dihapus.');
    }
}
