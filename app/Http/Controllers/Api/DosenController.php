<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DosenController extends Controller
{
    public function index(Request $request)
    {
        $dosen = Dosen::with('prodi')
            ->when($request->search, function ($query, $search) {
                $query->where('nidn', 'like', "%{$search}%")
                    ->orWhere('nama_depan', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('MasterData/Dosen/Index', [
            'dosen' => $dosen,
            'prodi_list' => \App\Models\Prodi::select('id', 'nama_prodi')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nidn' => 'required|string|unique:m_dosen,nidn',
            'nama_depan' => 'required|string',
            'nama_belakang' => 'nullable|string',
            'prodi_id' => 'required|exists:m_prodi,id',
        ]);

        Dosen::create($validated);

        return redirect()->back()->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function update(Request $request, Dosen $dosen)
    {
        $validated = $request->validate([
            'nidn' => 'required|string|unique:m_dosen,nidn,' . $dosen->id,
            'nama_depan' => 'required|string',
            'nama_belakang' => 'nullable|string',
            'prodi_id' => 'required|exists:m_prodi,id',
        ]);

        $dosen->update($validated);

        return redirect()->back()->with('success', 'Dosen berhasil diperbarui.');
    }

    public function destroy(Dosen $dosen)
    {
        $dosen->delete();

        return redirect()->back()->with('success', 'Dosen berhasil dihapus.');
    }
}
