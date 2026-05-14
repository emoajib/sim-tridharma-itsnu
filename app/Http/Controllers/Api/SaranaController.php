<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sarana;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaranaController extends Controller
{
    public function index(Request $request)
    {
        $sarana = Sarana::with('prodi')
            ->when($request->search, function ($query, $search) {
                $query->where('nama_sarana', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('Sarpras/Index', [
            'sarana' => $sarana,
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prodi_id' => 'required|exists:m_prodi,id',
            'nama_sarana' => 'required|string|max:255',
            'jenis_sarana' => 'required|string|max:100',
            'jumlah' => 'required|integer|min:1',
            'kondisi' => 'nullable|string|in:baik,sedang,rusak',
            'tanggal_kalibrasi' => 'nullable|date',
            'tanggal_kalibrasi_berikut' => 'nullable|date',
        ]);

        Sarana::create($validated);

        return redirect()->back()->with('success', 'Sarana berhasil ditambahkan.');
    }

    public function update(Request $request, Sarana $sarana)
    {
        $validated = $request->validate([
            'prodi_id' => 'required|exists:m_prodi,id',
            'nama_sarana' => 'required|string|max:255',
            'jenis_sarana' => 'required|string|max:100',
            'jumlah' => 'required|integer|min:1',
            'kondisi' => 'required|string|in:baik,sedang,rusak',
            'tanggal_kalibrasi' => 'nullable|date',
            'tanggal_kalibrasi_berikut' => 'nullable|date',
        ]);

        $sarana->update($validated);

        return redirect()->back()->with('success', 'Sarana berhasil diperbarui.');
    }

    public function destroy(Sarana $sarana)
    {
        $sarana->delete();

        return redirect()->back()->with('success', 'Sarana berhasil dihapus.');
    }
}
