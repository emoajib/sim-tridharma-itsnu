<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LayananMahasiswaRequest;
use App\Models\LayananMahasiswa;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LayananMahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $items = LayananMahasiswa::with('periode')
            ->when($request->jenis_layanan, fn($q, $v) => $q->where('jenis_layanan', $v))
            ->when($request->periode_id, fn($q, $v) => $q->where('periode_id', $v))
            ->paginate(10);

        return Inertia::render('Kemahasiswaan/LayananMahasiswa/Index', [
            'items' => $items,
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
        ]);
    }

    public function store(LayananMahasiswaRequest $request)
    {
        LayananMahasiswa::create($request->validated());

        return redirect()->back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(LayananMahasiswaRequest $request, LayananMahasiswa $layananMahasiswa)
    {
        $layananMahasiswa->update($request->validated());

        return redirect()->back()->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(LayananMahasiswa $layananMahasiswa)
    {
        $layananMahasiswa->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }
}
