<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rkat\ApprovalRequest;
use App\Http\Requests\Rkat\StoreUsulanRequest;
use App\Http\Requests\RkatRequest;
use App\Models\RkatPagu;
use App\Models\UsulanRkat;
use App\Models\PeriodeAkademik;
use App\Models\IndikatorIku;
use App\Models\Prodi;
use App\Models\Fakultas;
use App\Services\Rkat\RkatService;
use App\Traits\HasRoleScope;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RkatController extends Controller
{
    use HasRoleScope;

    public function __construct(
        protected RkatService $rkatService
    ) {}

    /**
     * Display listing (Inertia web or JSON API)
     */
    public function index(Request $request)
    {
        $query = UsulanRkat::with(['prodi', 'periode', 'iku', 'pengusul', 'logs']);

        $this->applyScope($query, $request->user(), 'prodi_id');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('prodi_id')) {
            $query->where('prodi_id', $request->prodi_id);
        }
        if ($request->has('periode_id')) {
            $query->where('periode_id', $request->periode_id);
        }

        if ($request->wantsJson() || $request->expectsJson()) {
            $proposals = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 20);
            return response()->json(['success' => true, 'data' => $proposals]);
        }

        $proposals = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return Inertia::render('Keuangan/Rkat/Index', [
            'proposals' => $proposals,
            'filters' => $request->only(['status', 'periode_id', 'prodi_id']),
            'periode_list' => PeriodeAkademik::orderBy('kode_periode', 'desc')->get(['id', 'kode_periode', 'nama_periode']),
            'prodi_list' => Prodi::all(['id', 'nama_prodi']),
        ]);
    }

    /**
     * Inertia page: create form
     */
    public function create()
    {
        return Inertia::render('Keuangan/Rkat/Create', [
            'periode_list' => PeriodeAkademik::orderBy('kode_periode', 'desc')->get(['id', 'kode_periode', 'nama_periode']),
            'iku_list' => IndikatorIku::where('is_active', true)->get(['id', 'nama_indikator', 'kode_iku']),
            'prodi_list' => Prodi::all(['id', 'nama_prodi']),
        ]);
    }

    /**
     * Store a newly created proposal.
     */
    public function store(StoreUsulanRequest $request)
    {
        $usulan = $this->rkatService->submitUsulan(
            $request->validated(),
            $request->user()->id
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Usulan RKAT berhasil diajukan.', 'data' => $usulan], 201);
        }

        return redirect()->route('rkat.index')->with('success', 'Usulan RKAT berhasil diajukan.');
    }

    /**
     * Inertia page: show detail
     */
    public function show(int $id)
    {
        $proposal = UsulanRkat::with(['prodi', 'periode', 'iku', 'indikatorAkreditasi', 'pengusul', 'logs.user'])->findOrFail($id);

        return Inertia::render('Keuangan/Rkat/Detail', [
            'proposal' => $proposal,
        ]);
    }

    /**
     * Process approval/rejection.
     */
    public function approve(ApprovalRequest $request, int $id)
    {
        try {
            $usulan = $this->rkatService->processApproval(
                $id,
                $request->action,
                $request->user()->id,
                $request->keterangan
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Usulan RKAT berhasil di-{$request->action}.",
                    'data' => $usulan,
                ]);
            }

            return redirect()->back()->with('success', "Usulan RKAT berhasil di-{$request->action}.");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Check budget ceiling (JSON API)
     */
    public function checkPagu(RkatRequest $request)
    {
        $validated = $request->validated();

        $status = $this->rkatService->checkPaguAvailability(
            $validated['prodi_id'],
            'Prodi',
            $validated['periode_id'],
            $validated['amount']
        );

        return response()->json(['success' => true, 'data' => $status]);
    }

    /**
     * Inertia page: manage pagu
     */
    public function paguIndex(Request $request)
    {
        $query = RkatPagu::with('periode');
        $paginations = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return Inertia::render('Keuangan/Rkat/Pagu', [
            'paginations' => $paginations,
            'periode_list' => PeriodeAkademik::orderBy('kode_periode', 'desc')->get(['id', 'kode_periode', 'nama_periode']),
            'prodi_list' => Prodi::all(['id', 'nama_prodi']),
            'fakultas_list' => Fakultas::all(['id', 'nama_fakultas']),
        ]);
    }

    public function paguStore(RkatRequest $request)
    {
        $validated = $request->validated();

        RkatPagu::updateOrCreate(
            ['periode_id' => $validated['periode_id'], 'unit_type' => $validated['unit_type'], 'unit_id' => $validated['unit_id']],
            ['pagu_total' => $validated['pagu_total']]
        );

        return redirect()->back()->with('success', 'Pagu anggaran berhasil disimpan.');
    }
}
