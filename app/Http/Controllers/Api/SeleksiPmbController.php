<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeleksiPmbRequest;
use App\Models\SeleksiPmb;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SeleksiPmbController extends Controller
{
    public function index(Request $request)
    {
        $items = SeleksiPmb::with(['periode', 'prodi'])
            ->when($request->search, fn($q, $s) => $q->whereHas('prodi', fn($qq) => $qq->where('nama_prodi', 'like', "%{$s}%")))
            ->paginate(10);

        return Inertia::render('Kemahasiswaan/SeleksiPmb/Index', [
            'items' => $items,
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
        ]);
    }

    public function store(SeleksiPmbRequest $request)
    {
        SeleksiPmb::create($request->validated());

        return redirect()->back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(SeleksiPmbRequest $request, SeleksiPmb $seleksiPmb)
    {
        $seleksiPmb->update($request->validated());

        return redirect()->back()->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(SeleksiPmb $seleksiPmb)
    {
        $seleksiPmb->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }
}
