<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SertifikatOstamaruRequest;
use App\Models\SertifikatOstamaru;
use App\Models\Mahasiswa;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SertifikatOstamaruController extends Controller
{
    public function index(Request $request)
    {
        $items = SertifikatOstamaru::with(['mahasiswa', 'periode'])
            ->when($request->search, fn($q, $s) => $q->where('nomor_sertifikat', 'like', "%{$s}%"))
            ->when($request->jenis_sertifikat, fn($q, $v) => $q->where('jenis_sertifikat', $v))
            ->paginate(10);

        return Inertia::render('Kemahasiswaan/SertifikatOstamaru/Index', [
            'items' => $items,
            'mahasiswa_list' => Mahasiswa::select('id', 'nim', 'nama')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
        ]);
    }

    public function store(SertifikatOstamaruRequest $request)
    {
        SertifikatOstamaru::create($request->validated());

        return redirect()->back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(SertifikatOstamaruRequest $request, SertifikatOstamaru $sertifikatOstamaru)
    {
        $sertifikatOstamaru->update($request->validated());

        return redirect()->back()->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(SertifikatOstamaru $sertifikatOstamaru)
    {
        $sertifikatOstamaru->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }

    public function download(SertifikatOstamaru $sertifikatOstamaru)
    {
        abort_unless($sertifikatOstamaru->is_downloadable, 403, 'Sertifikat tidak dapat diunduh.');

        return Storage::download($sertifikatOstamaru->file_sertifikat);
    }
}
