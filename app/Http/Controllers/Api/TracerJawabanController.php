<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TracerJawaban;
use App\Models\Alumni;
use App\Models\KuisionerTracer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TracerJawabanController extends Controller
{
    public function index(Request $request)
    {
        $jawaban = TracerJawaban::with(['alumni', 'kuisioner'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('alumni', function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%");
                });
            })
            ->paginate(10);

        return Inertia::render('Tracer/Jawaban/Index', [
            'jawaban' => $jawaban,
            'alumni_list' => Alumni::select('id', 'nama', 'nim')->get(),
            'kuisioner_list' => KuisionerTracer::select('id', 'judul_kuisioner', 'tahun')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'alumni_id' => 'required|exists:m_alumni,id',
            'kuisioner_id' => 'required|exists:m_kuisioner_tracer,id',
            'jawaban' => 'required|json',
        ]);

        TracerJawaban::create($validated);

        return redirect()->back()->with('success', 'Jawaban tracer berhasil ditambahkan.');
    }

    public function destroy(TracerJawaban $tracerJawaban)
    {
        $tracerJawaban->delete();

        return redirect()->back()->with('success', 'Jawaban tracer berhasil dihapus.');
    }
}
