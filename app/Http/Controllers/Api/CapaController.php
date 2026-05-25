<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CapaRequest;
use App\Models\AuditHistory;
use App\Models\Capa;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\User;
use App\Services\SPMI\SpmiWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CapaController extends Controller
{
    public function __construct(
        private SpmiWorkflowService $workflowService,
    ) {}

    /**
     * Display a paginated, filterable list of CAPAs.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $capa = Capa::with([
                'auditMutu.prodi', 'auditMutu.periode', 'picUser', 'verifiedBy',
            ])
            ->when($request->prodi_id, function ($q, $s) {
                $q->whereHas('auditMutu', function ($q) use ($s) {
                    $q->where('prodi_id', $s);
                });
            })
            ->when($request->status, function ($q, $s) {
                $q->where('status', $s);
            })
            ->when($request->pic_user_id, function ($q, $s) {
                $q->where('pic_user_id', $s);
            })
            ->when($request->overdue, function ($q) {
                $q->whereNotNull('corrective_deadline')
                    ->where('corrective_deadline', '<', now())
                    ->whereNotIn('status', ['verified', 'closed', 'archived']);
            })
            ->when($request->search, function ($q, $s) {
                $q->whereHas('auditMutu', function ($q) use ($s) {
                    $q->where('judul_audit', 'like', "%{$s}%");
                });
            })
            ->orderBy('created_at', 'DESC')
            ->paginate((int) $request->per_page ?: 10);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $capa,
            ]);
        }

        return Inertia::render('Spmi/Capa/Index', [
            'capa' => $capa,
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'user_list' => User::select('id', 'name')->get(),
            'filters' => $request->only(['prodi_id', 'status', 'pic_user_id', 'overdue', 'search']),
        ]);
    }

    /**
     * Display the specified CAPA with timeline.
     */
    public function show(Capa $capa): Response|JsonResponse
    {
        $capa->load([
            'auditMutu.prodi', 'auditMutu.periode', 'auditMutu.standarMutu',
            'picUser', 'verifiedBy',
        ]);

        // Load audit history timeline
        $timeline = AuditHistory::where('audit_mutu_id', $capa->audit_mutu_id)
            ->with('user')
            ->orderBy('created_at', 'DESC')
            ->get();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'capa' => $capa,
                    'timeline' => $timeline,
                ],
            ]);
        }

        return Inertia::render('Spmi/Capa/Show', [
            'capa' => $capa,
            'timeline' => $timeline,
        ]);
    }

    /**
     * Update the specified CAPA.
     */
    public function update(CapaRequest $request, Capa $capa)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($request, $capa, $validated) {
            // Handle file uploads
            if ($request->hasFile('corrective_evidence_file')) {
                if ($capa->corrective_evidence_file) {
                    Storage::disk('public')->delete($capa->corrective_evidence_file);
                }
                $validated['corrective_evidence_file'] = $request->file('corrective_evidence_file')
                    ->store('capa-evidence/corrective', 'public');
            }

            if ($request->hasFile('preventive_evidence_file')) {
                if ($capa->preventive_evidence_file) {
                    Storage::disk('public')->delete($capa->preventive_evidence_file);
                }
                $validated['preventive_evidence_file'] = $request->file('preventive_evidence_file')
                    ->store('capa-evidence/preventive', 'public');
            }

            $capa->update($validated);

            AuditHistory::create([
                'audit_mutu_id' => $capa->audit_mutu_id,
                'user_id' => auth()->id(),
                'field' => 'capa_updated',
                'old_value' => null,
                'new_value' => json_encode($validated),
                'action' => 'capa_updated',
            ]);

            Log::info('CAPA updated', [
                'capa_id' => $capa->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'CAPA berhasil diperbarui.');
        });
    }

    /**
     * Submit CAPA for verification.
     */
    public function submitVerification(Request $request, Capa $capa)
    {
        $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            $service = app(\App\Services\SPMI\CapaService::class);
            $service->submitForVerification($capa, auth()->id());

            Log::info('CAPA submitted for verification', [
                'capa_id' => $capa->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'CAPA berhasil diajukan untuk verifikasi.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['submit' => $e->getMessage()]);
        }
    }

    /**
     * Verify or reject a CAPA (LPM/auditor only).
     */
    public function verify(Request $request, Capa $capa)
    {
        $validated = $request->validate([
            'action' => 'required|in:approved,rejected',
            'note' => 'required|string|max:1000',
        ]);

        try {
            $service = app(\App\Services\SPMI\CapaService::class);
            $service->verify(
                $capa,
                auth()->id(),
                $validated['note'],
                $validated['action'] === 'approved'
            );

            Log::info('CAPA verification completed', [
                'capa_id' => $capa->id,
                'action' => $validated['action'],
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Verifikasi CAPA berhasil diproses.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['verify' => $e->getMessage()]);
        }
    }

    /**
     * Get timeline data for a CAPA (JSON).
     */
    public function getTimeline(Capa $capa): JsonResponse
    {
        $timeline = AuditHistory::where('audit_mutu_id', $capa->audit_mutu_id)
            ->with('user:id,name')
            ->orderBy('created_at', 'ASC')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'action' => $item->action,
                    'field' => $item->field,
                    'old_value' => $item->old_value,
                    'new_value' => $item->new_value,
                    'user_name' => $item->user?->name ?? 'System',
                    'created_at' => $item->created_at->toISOString(),
                    'created_at_formatted' => $item->created_at->format('d M Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $timeline,
        ]);
    }
}
