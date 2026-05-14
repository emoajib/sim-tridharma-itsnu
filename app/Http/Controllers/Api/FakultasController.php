<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_fakultas' => 'required|string|unique:m_fakultas,kode_fakultas',
            'nama_fakultas' => 'required|string',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
        ]);

        Fakultas::create($validated);

        return redirect()->back()->with('success', 'Fakultas berhasil ditambahkan.');
    }

    public function update(Request $request, Fakultas $fakultas)
    {
        $validated = $request->validate([
            'kode_fakultas' => 'required|string|unique:m_fakultas,kode_fakultas,' . $fakultas->id,
            'nama_fakultas' => 'required|string',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
        ]);

        $fakultas->update($validated);

        return redirect()->back()->with('success', 'Fakultas berhasil diperbarui.');
    }

    public function destroy(Fakultas $fakultas)
    {
        $fakultas->delete();

        return redirect()->back()->with('success', 'Fakultas berhasil dihapus.');
    }
}
