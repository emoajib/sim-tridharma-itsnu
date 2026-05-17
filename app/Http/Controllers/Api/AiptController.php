<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiptMetric;
use App\Models\SpmiCycle;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AiptController extends Controller
{
    public function index(Request $request)
    {
        $periodeId = $request->get('periode_id', PeriodeAkademik::where('is_active', true)->first()?->id);
        
        $metrics = AiptMetric::where('periode_id', $periodeId)->get();
        $cycles = SpmiCycle::orderBy('tanggal_mulai', 'desc')->get();
        
        // Aggregate aspects based on BAN-PT 4.0
        $aspects = [
            'Budaya Mutu' => $metrics->where('aspek', 'Budaya Mutu'),
            'Relevansi' => $metrics->where('aspek', 'Relevansi'),
            'Akuntabilitas' => $metrics->where('aspek', 'Akuntabilitas'),
            'Diferensiasi Misi' => $metrics->where('aspek', 'Diferensiasi Misi'),
        ];

        return Inertia::render('Aipt/Index', [
            'aspects' => $aspects,
            'spmi_cycles' => $cycles,
            'periode_list' => PeriodeAkademik::all(),
            'selected_periode_id' => $periodeId,
        ]);
    }
}
