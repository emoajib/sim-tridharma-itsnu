<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProdiRequest;
use App\Models\Fakultas;
use App\Models\LembagaAkreditasi;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProdiController extends Controller
{
    public function index(Request $request)
    {
        $prodi = Prodi::with(['fakultas', 'lembaga'])
            ->when($request->search, function ($query, $search) {
                $query->where('kode_prodi', 'like', "%{$search}%")
                    ->orWhere('nama_prodi', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('MasterData/Prodi/Index', [
            'prodi' => $prodi,
            'fakultas_list' => Fakultas::select('id', 'nama_fakultas')->get(),
            'lembaga_list' => LembagaAkreditasi::select('id', 'nama_lembaga', 'singkatan')->get(),
        ]);
    }

    public function store(ProdiRequest $request)
    {
        Prodi::create($request->validated());

        return redirect()->back()->with('success', 'Prodi berhasil ditambahkan.');
    }

    public function update(ProdiRequest $request, Prodi $prodi)
    {
        $prodi->update($request->validated());

        return redirect()->back()->with('success', 'Prodi berhasil diperbarui.');
    }

    public function destroy(Prodi $prodi)
    {
        $prodi->delete();

        return redirect()->back()->with('success', 'Prodi berhasil dihapus.');
    }
}
