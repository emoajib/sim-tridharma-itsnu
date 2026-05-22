<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportPreviewRequest;
use App\Http\Requests\InstrumenAkreditasiRequest;
use App\Imports\InstrumenCriteriaImport;
use App\Models\InstrumenAkreditasi;
use App\Models\LembagaAkreditasi;
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

    public function store(InstrumenAkreditasiRequest $request)
    {
        InstrumenAkreditasi::create($request->validated());

        return redirect()->back()->with('success', 'Instrumen berhasil ditambahkan.');
    }

    public function update(InstrumenAkreditasiRequest $request, InstrumenAkreditasi $instrumenAkreditasi)
    {
        $instrumenAkreditasi->update($request->validated());

        return redirect()->back()->with('success', 'Instrumen berhasil diperbarui.');
    }

    public function destroy(InstrumenAkreditasi $instrumenAkreditasi)
    {
        $instrumenAkreditasi->delete();

        return redirect()->back()->with('success', 'Instrumen berhasil dihapus.');
    }

    public function importPreview(ImportPreviewRequest $request)
    {
        try {
            $import = new InstrumenCriteriaImport;
            $data = Excel::toCollection($import, $request->file('file'));

            $criteria = $data->first()->map(function ($row) {
                return [
                    'kode' => $row['kode'] ?? $row['code'] ?? '',
                    'nama' => $row['nama_kriteria'] ?? $row['kriteria'] ?? $row['name'] ?? '',
                    'bobot' => (float) ($row['bobot'] ?? $row['weight'] ?? 1),
                ];
            })->filter(fn ($item) => ! empty($item['kode']))->values();

            return response()->json([
                'success' => true,
                'data' => $criteria,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
