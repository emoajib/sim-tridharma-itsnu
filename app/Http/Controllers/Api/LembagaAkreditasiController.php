<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LembagaAkreditasiRequest;
use App\Models\LembagaAkreditasi;
use Inertia\Inertia;

class LembagaAkreditasiController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/LembagaAkreditasi/Index', [
            'lembaga' => LembagaAkreditasi::withCount('prodi')->get(),
        ]);
    }

    public function store(LembagaAkreditasiRequest $request)
    {
        LembagaAkreditasi::create($request->validated());

        return redirect()->back()->with('success', 'Lembaga akreditasi berhasil ditambahkan.');
    }

    public function update(LembagaAkreditasiRequest $request, LembagaAkreditasi $lembagaAkreditasi)
    {
        $lembagaAkreditasi->update($request->validated());

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
