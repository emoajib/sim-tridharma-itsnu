<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FakultasRequest;
use App\Models\Fakultas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FakultasController extends Controller
{
    public function index(Request $request)
    {
        $fakultas = Fakultas::query()
            ->when($request->search, function ($query, $search) {
                $query->where('kode_fakultas', 'like', "%{$search}%")
                    ->orWhere('nama_fakultas', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('MasterData/Fakultas/Index', [
            'fakultas' => $fakultas,
        ]);
    }

    public function store(FakultasRequest $request)
    {
        Fakultas::create($request->validated());

        return redirect()->back()->with('success', 'Fakultas berhasil ditambahkan.');
    }

    public function update(FakultasRequest $request, Fakultas $fakultas)
    {
        $fakultas->update($request->validated());

        return redirect()->back()->with('success', 'Fakultas berhasil diperbarui.');
    }

    public function destroy(Fakultas $fakultas)
    {
        $fakultas->delete();

        return redirect()->back()->with('success', 'Fakultas berhasil dihapus.');
    }
}
