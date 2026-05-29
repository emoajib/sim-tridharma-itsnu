<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrestasiRequest;
use App\Models\Prestasi;
use App\Models\KategoriPrestasi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrestasiController extends Controller
{
    public function index(Request $request)
    {
        $items = Prestasi::with(['kategori', 'members.mahasiswa'])
            ->when($request->kategori_id, fn($q, $v) => $q->where('kategori_id', $v))
            ->when($request->tingkat, fn($q, $v) => $q->where('tingkat', $v))
            ->when($request->status_verifikasi, fn($q, $v) => $q->where('status_verifikasi', $v))
            ->when($request->search, fn($q, $s) => $q->where('nama_kompetisi', 'like', "%{$s}%"))
            ->paginate(10);

        return Inertia::render('Kemahasiswaan/Prestasi/Index', [
            'items' => $items,
            'kategori_list' => KategoriPrestasi::select('id', 'nama_kategori')->get(),
            'tingkat_list' => ['Lokal/Wilayah', 'Nasional', 'Internasional'],
        ]);
    }

    public function store(PrestasiRequest $request)
    {
        Prestasi::create($request->validated());

        return redirect()->back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function show(Prestasi $prestasi)
    {
        $prestasi->load(['kategori', 'members.mahasiswa']);

        return Inertia::render('Kemahasiswaan/Prestasi/Show', [
            'prestasi' => $prestasi,
        ]);
    }

    public function update(PrestasiRequest $request, Prestasi $prestasi)
    {
        $prestasi->update($request->validated());

        return redirect()->back()->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Prestasi $prestasi)
    {
        $prestasi->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }

    public function verify(Prestasi $prestasi)
    {
        abort_if($prestasi->status_verifikasi !== 'SUBMITTED', 422, 'Hanya prestasi dengan status SUBMITTED yang dapat diverifikasi.');

        $prestasi->update([
            'status_verifikasi' => 'VERIFIED',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Prestasi berhasil diverifikasi.');
    }

    public function requestRevision(Request $request, Prestasi $prestasi)
    {
        abort_if($prestasi->status_verifikasi !== 'SUBMITTED', 422, 'Hanya prestasi dengan status SUBMITTED yang dapat direvisi.');

        $request->validate(['catatan_reviewer' => 'required|string']);

        $prestasi->update([
            'status_verifikasi' => 'REVISION_REQUESTED',
            'catatan_reviewer' => $request->catatan_reviewer,
        ]);

        return redirect()->back()->with('success', 'Revisi telah diminta.');
    }
}
