<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DosenRequest;
use App\Models\Dosen;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
            'prodi_list' => Cache::remember('prodi_list', 3600, fn () => Prodi::select('id', 'nama_prodi')->get()),
        ]);
    }

    public function store(DosenRequest $request)
    {
        Dosen::create($request->validated());

        return redirect()->back()->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function update(DosenRequest $request, Dosen $dosen)
    {
        $dosen->update($request->validated());

        return redirect()->back()->with('success', 'Dosen berhasil diperbarui.');
    }

    public function destroy(Dosen $dosen)
    {
        $dosen->delete();

        return redirect()->back()->with('success', 'Dosen berhasil dihapus.');
    }
}
