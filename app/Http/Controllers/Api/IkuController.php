<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IndikatorIku;
use App\Models\LembagaAkreditasi;
use Illuminate\Http\Request;

class IkuController extends Controller
{
    public function index(Request $request)
    {
        $iku = IndikatorIku::with('lembaga')
            ->when($request->search, fn($q, $v) => $q->where('nama_indikator', 'like', "%{$v}%"))
            ->paginate(10);

        if (request()->wantsJson()) {
            return response()->json($iku);
        }

        return inertia('Iku/Index', [
            'iku' => $iku,
            'lembaga_list' => LembagaAkreditasi::all(['id', 'nama_lembaga', 'singkatan']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_iku' => 'required|string|max:50|unique:m_indikator_iku,kode_iku',
            'nama_indikator' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'lembaga_id' => 'nullable|exists:m_lembaga_akreditasi,id',
            'bobot' => 'nullable|numeric|min:0|max:100',
            'target' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $iku = IndikatorIku::create($validated);

        return redirect()->route('iku.index')->with('success', 'IKU berhasil dibuat.');
    }

    public function update(Request $request, IndikatorIku $iku)
    {
        $validated = $request->validate([
            'kode_iku' => 'required|string|max:50|unique:m_indikator_iku,kode_iku,' . $iku->id,
            'nama_indikator' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'lembaga_id' => 'nullable|exists:m_lembaga_akreditasi,id',
            'bobot' => 'nullable|numeric|min:0|max:100',
            'target' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $iku->update($validated);

        return redirect()->route('iku.index')->with('success', 'IKU berhasil diperbarui.');
    }

    public function destroy(IndikatorIku $iku)
    {
        $iku->delete();

        return redirect()->route('iku.index')->with('success', 'IKU berhasil dihapus.');
    }
}
