<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CplRequest;
use App\Models\Cpl;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CplController extends Controller
{
    public function index(Request $request)
    {
        $cpl = Cpl::with('prodi')
            ->when($request->search, function ($query, $search) {
                $query->where('kode_cpl', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('MasterData/Cpl/Index', [
            'cpl' => $cpl,
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
        ]);
    }

    public function store(CplRequest $request)
    {
        Cpl::create($request->validated());

        return redirect()->back()->with('success', 'CPL berhasil ditambahkan.');
    }

    public function update(CplRequest $request, Cpl $cpl)
    {
        $cpl->update($request->validated());

        return redirect()->back()->with('success', 'CPL berhasil diperbarui.');
    }

    public function destroy(Cpl $cpl)
    {
        $cpl->delete();

        return redirect()->back()->with('success', 'CPL berhasil dihapus.');
    }
}
