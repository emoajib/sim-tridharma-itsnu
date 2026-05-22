<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaranaRequest;
use App\Models\Prodi;
use App\Models\Sarana;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaranaController extends Controller
{
    public function index(Request $request)
    {
        $sarana = Sarana::with('prodi')
            ->when($request->search, function ($query, $search) {
                $query->where('nama_sarana', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('Sarpras/Index', [
            'sarana' => $sarana,
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
        ]);
    }

    public function store(SaranaRequest $request)
    {
        Sarana::create($request->validated());

        return redirect()->back()->with('success', 'Sarana berhasil ditambahkan.');
    }

    public function update(SaranaRequest $request, Sarana $sarana)
    {
        $sarana->update($request->validated());

        return redirect()->back()->with('success', 'Sarana berhasil diperbarui.');
    }

    public function destroy(Sarana $sarana)
    {
        $sarana->delete();

        return redirect()->back()->with('success', 'Sarana berhasil dihapus.');
    }
}
