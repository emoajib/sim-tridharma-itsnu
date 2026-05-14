<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PeriodeAkademikController extends Controller
{
    public function index(Request $request)
    {
        $periodeAkademik = PeriodeAkademik::query()
            ->when($request->search, function ($query, $search) {
                $query->where('kode_periode', 'like', "%{$search}%")
                    ->orWhere('nama_periode', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('MasterData/PeriodeAkademik/Index', [
            'periodeAkademik' => $periodeAkademik,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_periode' => 'required|string|unique:m_periode_akademik,kode_periode',
            'nama_periode' => 'required|string',
        ]);

        PeriodeAkademik::create($validated);

        return redirect()->back()->with('success', 'Periode akademik berhasil ditambahkan.');
    }

    public function update(Request $request, PeriodeAkademik $periodeAkademik)
    {
        $validated = $request->validate([
            'kode_periode' => 'required|string|unique:m_periode_akademik,kode_periode,' . $periodeAkademik->id,
            'nama_periode' => 'required|string',
        ]);

        $periodeAkademik->update($validated);

        return redirect()->back()->with('success', 'Periode akademik berhasil diperbarui.');
    }

    public function destroy(PeriodeAkademik $periodeAkademik)
    {
        $periodeAkademik->delete();

        return redirect()->back()->with('success', 'Periode akademik berhasil dihapus.');
    }
}
