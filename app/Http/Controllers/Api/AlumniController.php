<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AlumniRequest;
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

    public function store(AlumniRequest $request)
    {
        Alumni::create($request->validated());

        return redirect()->back()->with('success', 'Alumni berhasil ditambahkan.');
    }

    public function update(AlumniRequest $request, Alumni $alumni)
    {
        $alumni->update($request->validated());

        return redirect()->back()->with('success', 'Alumni berhasil diperbarui.');
    }

    public function destroy(Alumni $alumni)
    {
        $alumni->delete();

        return redirect()->back()->with('success', 'Alumni berhasil dihapus.');
    }
}
