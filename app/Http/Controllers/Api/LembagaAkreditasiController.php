<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LembagaAkreditasi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LembagaAkreditasiController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/LembagaAkreditasi/Index', [
            'lembaga' => LembagaAkreditasi::withCount('prodi')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lembaga' => 'required|string|max:100',
            'singkatan' => 'required|string|max:20|unique:m_lembaga_akreditasi,singkatan',
            'deskripsi' => 'nullable|string',
        ]);

        LembagaAkreditasi::create($validated);

        return redirect()->back()->with('success', 'Lembaga akreditasi berhasil ditambahkan.');
    }

    public function update(Request $request, LembagaAkreditasi $lembagaAkreditasi)
    {
        $validated = $request->validate([
            'nama_lembaga' => 'required|string|max:100',
            'singkatan' => 'required|string|max:20|unique:m_lembaga_akreditasi,singkatan,' . $lembagaAkreditasi->id,
            'deskripsi' => 'nullable|string',
        ]);

        $lembagaAkreditasi->update($validated);

        return redirect()->back()->with('success', 'Lembaga akreditasi berhasil diperbarui.');
    }

    public function destroy(LembagaAkreditasi $lembagaAkreditasi)
    {
        if ($lembagaAkreditasi->prodi()->count() > 0) {
            return redirect()->back()->with('error', 'Lembaga tidak bisa dihapus karena masih memiliki prodi yang terdaftar.');
        }

        $lembagaAkreditasi->delete();

        return redirect()->back()->with('success', 'Lembaga akreditasi berhasil dihapus.');
    }
}
