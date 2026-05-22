<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KurikulumRequest;
use App\Models\Kurikulum;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KurikulumController extends Controller
{
    public function index(Request $request)
    {
        $kurikulum = Kurikulum::with('prodi')
            ->when($request->search, function ($query, $search) {
                $query->where('nama_kurikulum', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('MasterData/Kurikulum/Index', [
            'kurikulum' => $kurikulum,
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
        ]);
    }

    public function store(KurikulumRequest $request)
    {
        Kurikulum::create($request->validated());

        return redirect()->back()->with('success', 'Kurikulum berhasil ditambahkan.');
    }

    public function update(KurikulumRequest $request, Kurikulum $kurikulum)
    {
        $kurikulum->update($request->validated());

        return redirect()->back()->with('success', 'Kurikulum berhasil diperbarui.');
    }

    public function destroy(Kurikulum $kurikulum)
    {
        $kurikulum->delete();

        return redirect()->back()->with('success', 'Kurikulum berhasil dihapus.');
    }
}
