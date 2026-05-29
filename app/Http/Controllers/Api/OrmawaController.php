<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrmawaRequest;
use App\Models\Ormawa;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrmawaController extends Controller
{
    public function index(Request $request)
    {
        $items = Ormawa::with(['prodi', 'pembinas.dosen'])
            ->when($request->search, fn($q, $s) => $q->where('nama', 'like', "%{$s}%")
                ->orWhere('kategori', 'like', "%{$s}%"))
            ->paginate(10);

        return Inertia::render('Kemahasiswaan/Ormawa/Index', [
            'items' => $items,
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
        ]);
    }

    public function store(OrmawaRequest $request)
    {
        Ormawa::create($request->validated());

        return redirect()->back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(OrmawaRequest $request, Ormawa $ormawa)
    {
        $ormawa->update($request->validated());

        return redirect()->back()->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Ormawa $ormawa)
    {
        $ormawa->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }
}
