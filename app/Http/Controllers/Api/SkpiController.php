<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SkpiRequest;
use App\Models\Skpi;
use App\Models\Mahasiswa;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SkpiController extends Controller
{
    public function index(Request $request)
    {
        $items = Skpi::with(['mahasiswa', 'periode'])
            ->when($request->status_verifikasi, fn($q, $v) => $q->where('status_verifikasi', $v))
            ->when($request->search, fn($q, $s) => $q->where('nama_kegiatan', 'like', "%{$s}%"))
            ->paginate(10);

        return Inertia::render('Kemahasiswaan/Skpi/Index', [
            'items' => $items,
            'mahasiswa_list' => Mahasiswa::select('id', 'nim', 'nama')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
        ]);
    }

    public function store(SkpiRequest $request)
    {
        Skpi::create($request->validated());

        return redirect()->back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(SkpiRequest $request, Skpi $skpi)
    {
        $skpi->update($request->validated());

        return redirect()->back()->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Skpi $skpi)
    {
        $skpi->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }

    public function verify(Skpi $skpi)
    {
        abort_if($skpi->status_verifikasi !== 'SUBMITTED', 422, 'Hanya SKPI dengan status SUBMITTED yang dapat diverifikasi.');

        $skpi->update([
            'status_verifikasi' => 'VERIFIED',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return redirect()->back()->with('success', 'SKPI berhasil diverifikasi.');
    }
}
