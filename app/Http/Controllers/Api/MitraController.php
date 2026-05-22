<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MitraRequest;
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

        return Inertia::render('Mitra/Index', [
            'mitra' => $mitra,
        ]);
    }

    public function store(MitraRequest $request)
    {
        Mitra::create($request->validated());

        return redirect()->back()->with('success', 'Mitra berhasil ditambahkan.');
    }

    public function update(MitraRequest $request, Mitra $mitra)
    {
        $mitra->update($request->validated());

        return redirect()->back()->with('success', 'Mitra berhasil diperbarui.');
    }

    public function destroy(Mitra $mitra)
    {
        $mitra->delete();

        return redirect()->back()->with('success', 'Mitra berhasil dihapus.');
    }
}
