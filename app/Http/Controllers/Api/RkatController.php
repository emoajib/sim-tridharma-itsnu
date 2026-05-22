<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rkat\ApprovalRequest;
use App\Http\Requests\Rkat\StoreUsulanRequest;
use App\Services\Rkat\RkatService;
use Exception;
use Illuminate\Http\Request;

class RkatController extends Controller
{
    public function __construct(
        protected RkatService $rkatService
    ) {}

    /**
     * Display a listing of proposals for a prodi.
     */
    public function index(Request $request)
    {
        $request->validate([
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'nullable|exists:m_periode_akademik,id',
        ]);

        $proposals = $this->rkatService->getProposalsByUnit(
            $request->prodi_id,
            $request->periode_id
        );

        return response()->json([
            'success' => true,
            'data' => $proposals,
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

        return response()->json([
            'success' => true,
            'message' => 'Usulan RKAT berhasil diajukan.',
            'data' => $usulan,
        ], 201);
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

            return response()->json([
                'success' => true,
                'message' => "Usulan RKAT berhasil di-{$request->action}.",
                'data' => $usulan,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Check budget ceiling status.
     */
    public function checkPagu(Request $request)
    {
        $request->validate([
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $status = $this->rkatService->checkPaguAvailability(
            $request->prodi_id,
            'Prodi',
            $request->periode_id,
            $request->amount
        );

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }
}
