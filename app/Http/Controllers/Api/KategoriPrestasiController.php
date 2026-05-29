<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KategoriPrestasiRequest;
use App\Models\KategoriPrestasi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KategoriPrestasiController extends Controller
{
    public function index(Request $request)
    {
        $items = KategoriPrestasi::withCount('prestasis')
            ->when($request->search, fn($q, $s) => $q->where('nama_kategori', 'like', "%{$s}%"))
            ->paginate(10);

        return Inertia::render('Kemahasiswaan/KategoriPrestasi/Index', [
            'items' => $items,
        ]);
    }

    public function store(KategoriPrestasiRequest $request)
    {
        KategoriPrestasi::create($request->validated());

        return redirect()->back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(KategoriPrestasiRequest $request, KategoriPrestasi $kategoriPrestasi)
    {
        $kategoriPrestasi->update($request->validated());

        return redirect()->back()->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(KategoriPrestasi $kategoriPrestasi)
    {
        $kategoriPrestasi->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }
}
