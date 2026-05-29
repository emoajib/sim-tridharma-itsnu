<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AsetOrmawaRequest;
use App\Models\AsetOrmawa;
use App\Models\Ormawa;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AsetOrmawaController extends Controller
{
    public function index(Request $request)
    {
        $items = AsetOrmawa::with('ormawa')
            ->when($request->ormawa_id, fn($q, $v) => $q->where('ormawa_id', $v))
            ->paginate(10);

        return Inertia::render('Kemahasiswaan/AsetOrmawa/Index', [
            'items' => $items,
            'ormawa_list' => Ormawa::select('id', 'nama')->get(),
        ]);
    }

    public function store(AsetOrmawaRequest $request)
    {
        AsetOrmawa::create($request->validated());

        return redirect()->back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(AsetOrmawaRequest $request, AsetOrmawa $asetOrmawa)
    {
        $asetOrmawa->update($request->validated());

        return redirect()->back()->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(AsetOrmawa $asetOrmawa)
    {
        $asetOrmawa->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }
}
