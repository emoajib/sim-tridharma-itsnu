<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IndikatorAkreditasi;
use App\Models\InstrumenAkreditasi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndikatorAkreditasiController extends Controller
{
    public function index(Request $request)
    {
        $query = IndikatorAkreditasi::with('instrumen.lembaga');

        if ($request->search) {
            $query->where('nama_indikator', 'like', "%{$request->search}%")
                  ->orWhere('kode_indikator', 'like', "%{$request->search}%");
        }

        if ($request->instrumen_id) {
            $query->where('instrumen_id', $request->instrumen_id);
        }

        return Inertia::render('Admin/IndikatorAkreditasi/Index', [
            'indikator' => $query->paginate(15),
            'instrumen_list' => InstrumenAkreditasi::with('lembaga')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'instrumen_id' => 'required|exists:m_instrumen_akreditasi,id',
            'kode_indikator' => 'required|string|unique:m_indikator_akreditasi,kode_indikator',
            'nama_indikator' => 'required|string',
            'kriteria' => 'required|string',
            'bobot' => 'required|numeric',
            'target' => 'nullable|string',
            'jenis_akreditasi' => 'required|string',
        ]);

        IndikatorAkreditasi::create($validated);

        return redirect()->back()->with('success', 'Indikator berhasil ditambahkan.');
    }

    public function update(Request $request, IndikatorAkreditasi $indikatorAkreditasi)
    {
        $validated = $request->validate([
            'instrumen_id' => 'required|exists:m_instrumen_akreditasi,id',
            'kode_indikator' => 'required|string|unique:m_indikator_akreditasi,kode_indikator,' . $indikatorAkreditasi->id,
            'nama_indikator' => 'required|string',
            'kriteria' => 'required|string',
            'bobot' => 'required|numeric',
            'target' => 'nullable|string',
            'jenis_akreditasi' => 'required|string',
        ]);

        $indikatorAkreditasi->update($validated);

        return redirect()->back()->with('success', 'Indikator berhasil diperbarui.');
    }

    public function destroy(IndikatorAkreditasi $indikatorAkreditasi)
    {
        $indikatorAkreditasi->delete();
        return redirect()->back()->with('success', 'Indikator berhasil dihapus.');
    }
}
