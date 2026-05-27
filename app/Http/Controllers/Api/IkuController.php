<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IkuRequest;
use App\Models\IndikatorIku;
use App\Models\LembagaAkreditasi;
use Illuminate\Http\Request;

class IkuController extends Controller
{
    public function index(Request $request)
    {
        $iku = IndikatorIku::with('lembaga')
            ->when($request->search, fn($q, $v) => $q->where('nama_indikator', 'like', "%{$v}%"))
            ->paginate(10);

        if (request()->wantsJson()) {
            return response()->json($iku);
        }

        return inertia('Iku/Index', [
            'iku' => $iku,
            'lembaga_list' => LembagaAkreditasi::all(['id', 'nama_lembaga', 'singkatan']),
        ]);
    }

    public function store(IkuRequest $request)
    {
        $iku = IndikatorIku::create($request->validated());

        return redirect()->route('iku.index')->with('success', 'IKU berhasil dibuat.');
    }

    public function update(IkuRequest $request, IndikatorIku $iku)
    {
        $iku->update($request->validated());

        return redirect()->route('iku.index')->with('success', 'IKU berhasil diperbarui.');
    }

    public function destroy(IndikatorIku $iku)
    {
        $iku->delete();

        return redirect()->route('iku.index')->with('success', 'IKU berhasil dihapus.');
    }
}
