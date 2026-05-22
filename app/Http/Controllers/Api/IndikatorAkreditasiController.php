<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndikatorAkreditasiRequest;
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

    public function store(IndikatorAkreditasiRequest $request)
    {
        IndikatorAkreditasi::create($request->validated());

        return redirect()->back()->with('success', 'Indikator berhasil ditambahkan.');
    }

    public function update(IndikatorAkreditasiRequest $request, IndikatorAkreditasi $indikatorAkreditasi)
    {
        $indikatorAkreditasi->update($request->validated());

        return redirect()->back()->with('success', 'Indikator berhasil diperbarui.');
    }

    public function destroy(IndikatorAkreditasi $indikatorAkreditasi)
    {
        $indikatorAkreditasi->delete();

        return redirect()->back()->with('success', 'Indikator berhasil dihapus.');
    }
}
