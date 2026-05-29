<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PembinaOrmawaRequest;
use App\Models\PembinaOrmawa;
use App\Models\Ormawa;
use App\Models\Dosen;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PembinaOrmawaController extends Controller
{
    public function index(Request $request)
    {
        $items = PembinaOrmawa::with(['ormawa', 'dosen', 'periode'])
            ->when($request->ormawa_id, fn($q, $v) => $q->where('ormawa_id', $v))
            ->paginate(10);

        return Inertia::render('Kemahasiswaan/PembinaOrmawa/Index', [
            'items' => $items,
            'ormawa_list' => Ormawa::select('id', 'nama')->get(),
            'dosen_list' => Dosen::select('id', 'nidn', 'nama_depan', 'nama_belakang')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
        ]);
    }

    public function store(PembinaOrmawaRequest $request)
    {
        PembinaOrmawa::create($request->validated());

        return redirect()->back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(PembinaOrmawaRequest $request, PembinaOrmawa $pembinaOrmawa)
    {
        $pembinaOrmawa->update($request->validated());

        return redirect()->back()->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(PembinaOrmawa $pembinaOrmawa)
    {
        $pembinaOrmawa->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }
}
