<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        $alumni = Alumni::with('prodi')
            ->when($request->search, function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('Alumni/Index', [
            'alumni' => $alumni,
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nim' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'prodi_id' => 'required|exists:m_prodi,id',
            'tahun_lulus' => 'required|string|max:4',
            'masa_tunggu' => 'nullable|integer|min:0',
            'gaji_pertama' => 'nullable|numeric|min:0',
            'pekerjaan' => 'nullable|string|max:255',
        ]);

        Alumni::create($validated);

        return redirect()->back()->with('success', 'Alumni berhasil ditambahkan.');
    }

    public function update(Request $request, Alumni $alumni)
    {
        $validated = $request->validate([
            'nim' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'prodi_id' => 'required|exists:m_prodi,id',
            'tahun_lulus' => 'required|string|max:4',
            'masa_tunggu' => 'nullable|integer|min:0',
            'gaji_pertama' => 'nullable|numeric|min:0',
            'pekerjaan' => 'nullable|string|max:255',
        ]);

        $alumni->update($validated);

        return redirect()->back()->with('success', 'Alumni berhasil diperbarui.');
    }

    public function destroy(Alumni $alumni)
    {
        $alumni->delete();

        return redirect()->back()->with('success', 'Alumni berhasil dihapus.');
    }
}
