<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mitra;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MitraController extends Controller
{
    public function index(Request $request)
    {
        $mitra = Mitra::when($request->search, function ($query, $search) {
            $query->where('nama_mitra', 'like', "%{$search}%");
        })->paginate(10);

        return Inertia::render('Kerjasama/Mitra/Index', [
            'mitra' => $mitra,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mitra' => 'required|string|max:200',
            'jenis_mitra' => 'required|string|max:50',
            'alamat' => 'nullable|string',
            'kontak' => 'nullable|string|max:100',
            'telepon' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        Mitra::create($validated);

        return redirect()->back()->with('success', 'Mitra berhasil ditambahkan.');
    }

    public function update(Request $request, Mitra $mitra)
    {
        $validated = $request->validate([
            'nama_mitra' => 'required|string|max:200',
            'jenis_mitra' => 'required|string|max:50',
            'alamat' => 'nullable|string',
            'kontak' => 'nullable|string|max:100',
            'telepon' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $mitra->update($validated);

        return redirect()->back()->with('success', 'Mitra berhasil diperbarui.');
    }

    public function destroy(Mitra $mitra)
    {
        $mitra->delete();

        return redirect()->back()->with('success', 'Mitra berhasil dihapus.');
    }
}
