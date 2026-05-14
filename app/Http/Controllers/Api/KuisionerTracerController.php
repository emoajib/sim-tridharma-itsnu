<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KuisionerTracer;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KuisionerTracerController extends Controller
{
    public function index(Request $request)
    {
        $kuisioners = KuisionerTracer::with('prodi')
            ->when($request->search, function ($query, $search) {
                $query->where('judul_kuisioner', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('Tracer/Kuisioner/Index', [
            'kuisioners' => $kuisioners,
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prodi_id' => 'required|exists:m_prodi,id',
            'judul_kuisioner' => 'required|string|max:255',
            'tahun' => 'required|string|max:4',
            'pertanyaan' => 'required|json',
        ]);

        KuisionerTracer::create($validated);

        return redirect()->back()->with('success', 'Kuisioner tracer berhasil ditambahkan.');
    }

    public function update(Request $request, KuisionerTracer $kuisionerTracer)
    {
        $validated = $request->validate([
            'prodi_id' => 'required|exists:m_prodi,id',
            'judul_kuisioner' => 'required|string|max:255',
            'tahun' => 'required|string|max:4',
            'pertanyaan' => 'required|json',
        ]);

        $kuisionerTracer->update($validated);

        return redirect()->back()->with('success', 'Kuisioner tracer berhasil diperbarui.');
    }

    public function destroy(KuisionerTracer $kuisionerTracer)
    {
        $kuisionerTracer->delete();

        return redirect()->back()->with('success', 'Kuisioner tracer berhasil dihapus.');
    }
}
