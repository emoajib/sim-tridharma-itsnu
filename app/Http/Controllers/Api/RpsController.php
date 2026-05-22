<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RpsRequest;
use App\Models\MataKuliah;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\Rps;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RpsController extends Controller
{
    public function index(Request $request)
    {
        $rps = Rps::with('mataKuliah', 'prodi', 'periode')
            ->when($request->search, function ($q, $s) {
                $q->whereHas('mataKuliah', fn ($q) => $q->where('nama_mk', 'like', "%{$s}%"))
                    ->orWhere('kode_rps', 'like', "%{$s}%");
            })
            ->paginate(10);

        return Inertia::render('Kurikulum/Rps/Index', [
            'rps' => $rps,
            'mk_list' => MataKuliah::select('id', 'kode_mk', 'nama_mk')->get(),
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
        ]);
    }

    public function store(RpsRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('rps', 'public');
        }

        Rps::create($data);

        return redirect()->back()->with('success', 'RPS berhasil ditambahkan.');
    }

    public function update(RpsRequest $request, Rps $rp)
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            if ($rp->file_path) {
                \Storage::disk('public')->delete($rp->file_path);
            }
            $data['file_path'] = $request->file('file')->store('rps', 'public');
        }

        $rp->update($data);

        return redirect()->back()->with('success', 'RPS berhasil diperbarui.');
    }

    public function destroy(Rps $rp)
    {
        if ($rp->file_path) {
            \Storage::disk('public')->delete($rp->file_path);
        }
        $rp->delete();

        return redirect()->back()->with('success', 'RPS berhasil dihapus.');
    }
}
