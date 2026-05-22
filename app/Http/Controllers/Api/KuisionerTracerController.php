<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KuisionerTracerRequest;
use App\Models\KuisionerTracer;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KuisionerTracerController extends Controller
{
    public function index(Request $request)
    {
        $kuisioner = KuisionerTracer::with('prodi')
            ->when($request->search, function ($query, $search) {
                $query->where('judul_kuisioner', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('Tracer/Kuisioner/Index', [
            'kuisioner' => $kuisioner,
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
        ]);
    }

    public function store(KuisionerTracerRequest $request)
    {
        KuisionerTracer::create($request->validated());

        return redirect()->back()->with('success', 'Kuisioner berhasil ditambahkan.');
    }

    public function update(KuisionerTracerRequest $request, KuisionerTracer $kuisionerTracer)
    {
        $kuisionerTracer->update($request->validated());

        return redirect()->back()->with('success', 'Kuisioner berhasil diperbarui.');
    }

    public function destroy(KuisionerTracer $kuisionerTracer)
    {
        $kuisionerTracer->delete();

        return redirect()->back()->with('success', 'Kuisioner berhasil dihapus.');
    }
}
