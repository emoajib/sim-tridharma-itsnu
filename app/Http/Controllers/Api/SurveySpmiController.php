<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PeriodeAkademik;
use App\Models\SurveySpmi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SurveySpmiController extends Controller
{
    /**
     * Display a list of survey SPMI.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $surveys = SurveySpmi::with('periode')
            ->when($request->periode_id, function ($q, $s) {
                $q->where('periode_id', $s);
            })
            ->when($request->responden_type, function ($q, $s) {
                $q->where('responden_type', $s);
            })
            ->when($request->search, function ($q, $s) {
                $q->where('token', 'like', "%{$s}%");
            })
            ->orderBy('created_at', 'DESC')
            ->paginate((int) $request->per_page ?: 10);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $surveys,
            ]);
        }

        return Inertia::render('Spmi/Survey/Index', [
            'surveys' => $surveys,
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
            'filters' => $request->only(['periode_id', 'responden_type', 'search']),
        ]);
    }

    /**
     * Store a newly created survey entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'responden_type' => 'required|string|in:dosen,tenaga_kependidikan,mahasiswa,alumni,mitra',
            'responses' => 'nullable|array',
            'skor_rata_rata' => 'nullable|numeric|min:0|max:100',
        ]);

        return DB::transaction(function () use ($validated) {
            $validated['responses'] = $validated['responses'] ?? [];
            $validated['token'] = Str::random(32);

            $survey = SurveySpmi::create($validated);

            Log::info('Survey SPMI created', [
                'survey_id' => $survey->id,
                'responden_type' => $survey->responden_type,
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Survey SPMI berhasil ditambahkan.');
        });
    }
}
