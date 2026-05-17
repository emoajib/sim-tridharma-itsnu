<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InstrumenAkreditasi;
use App\Models\LembagaAkreditasi;
use App\Imports\InstrumenCriteriaImport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class InstrumenAkreditasiController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/InstrumenAkreditasi/Index', [
            'instrumen' => InstrumenAkreditasi::with('lembaga')->get(),
            'lembaga_list' => LembagaAkreditasi::select('id', 'singkatan')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lembaga_id' => 'required|exists:m_lembaga_akreditasi,id',
            'nama_instrumen' => 'required|string|max:100',
            'matriks_kriteria' => 'nullable|array',
        ]);

        InstrumenAkreditasi::create($validated);

        return redirect()->back()->with('success', 'Instrumen berhasil ditambahkan.');
    }

    public function update(Request $request, InstrumenAkreditasi $instrumenAkreditasi)
    {
        $validated = $request->validate([
            'lembaga_id' => 'required|exists:m_lembaga_akreditasi,id',
            'nama_instrumen' => 'required|string|max:100',
            'matriks_kriteria' => 'nullable|array',
        ]);

        $instrumenAkreditasi->update($validated);

        return redirect()->back()->with('success', 'Instrumen berhasil diperbarui.');
    }

    public function destroy(InstrumenAkreditasi $instrumenAkreditasi)
    {
        $instrumenAkreditasi->delete();

        return redirect()->back()->with('success', 'Instrumen berhasil dihapus.');
    }

    /**
     * Preview logic for Excel Import
     * Parses the file and returns the data for review in the UI
     */
    public function importPreview(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        try {
            $import = new InstrumenCriteriaImport();
            $data = Excel::toCollection($import, $request->file('file'));
            
            // Flatten and clean the data
            $criteria = $data->first()->map(function($row) {
                return [
                    'kode' => $row['kode'] ?? $row['code'] ?? '',
                    'nama' => $row['nama_kriteria'] ?? $row['kriteria'] ?? $row['name'] ?? '',
                    'bobot' => (float) ($row['bobot'] ?? $row['weight'] ?? 1),
                ];
            })->filter(fn($item) => !empty($item['kode']))->values();

            return response()->json([
                'success' => true,
                'data' => $criteria
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
