<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrestasiMemberRequest;
use App\Models\PrestasiMember;
use App\Models\Prestasi;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrestasiMemberController extends Controller
{
    public function index(Request $request)
    {
        $items = PrestasiMember::with(['prestasi', 'mahasiswa'])
            ->when($request->prestasi_id, fn($q, $v) => $q->where('prestasi_id', $v))
            ->paginate(10);

        return Inertia::render('Kemahasiswaan/PrestasiMember/Index', [
            'items' => $items,
            'prestasi_list' => Prestasi::select('id', 'nama_kompetisi')->get(),
            'mahasiswa_list' => Mahasiswa::select('id', 'nim', 'nama')->get(),
        ]);
    }

    public function store(PrestasiMemberRequest $request)
    {
        PrestasiMember::create($request->validated());

        return redirect()->back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(PrestasiMemberRequest $request, PrestasiMember $prestasiMember)
    {
        $prestasiMember->update($request->validated());

        return redirect()->back()->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(PrestasiMember $prestasiMember)
    {
        $prestasiMember->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }
}
