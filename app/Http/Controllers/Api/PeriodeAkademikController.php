<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeriodeAkademikRequest;
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

    public function store(PeriodeAkademikRequest $request)
    {
        PeriodeAkademik::create($request->validated());

        return redirect()->back()->with('success', 'Periode akademik berhasil ditambahkan.');
    }

    public function update(PeriodeAkademikRequest $request, PeriodeAkademik $periodeAkademik)
    {
        $periodeAkademik->update($request->validated());

        return redirect()->back()->with('success', 'Periode akademik berhasil diperbarui.');
    }

    public function destroy(PeriodeAkademik $periodeAkademik)
    {
        $periodeAkademik->delete();

        return redirect()->back()->with('success', 'Periode akademik berhasil dihapus.');
    }
}
