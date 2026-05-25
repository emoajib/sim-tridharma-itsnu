<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditMutuRequest;
use App\Models\AuditHistory;
use App\Models\AuditMutu;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\StandarMutu;
use App\Models\User;
use App\Services\SPMI\AuditAnalysisService;
use App\Services\SPMI\CapaService;
use App\Services\SPMI\SpmiWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuditMutuController extends Controller
{
    public function __construct(
        private SpmiWorkflowService $workflowService,
        private AuditAnalysisService $analysisService,
        private CapaService $capaService,
    ) {}

    /**
     * Display a paginated, filterable list of audit mutu.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $audit = AuditMutu::with([
                'prodi', 'periode', 'standarMutu', 'picUser', 'capas',
            ])
            ->when($request->search, function ($q, $s) {
                $q->where('judul_audit', 'like', "%{$s}%")
                    ->orWhere('auditor', 'like', "%{$s}%");
            })
            ->when($request->status, function ($q, $s) {
                $q->where('status', $s);
            })
            ->when($request->standar_mutu_id, function ($q, $s) {
                $q->where('standar_mutu_id', $s);
            })
            ->when($request->severity, function ($q, $s) {
                $q->where('severity', $s);
            })
            ->when($request->pic_user_id, function ($q, $s) {
                $q->where('pic_user_id', $s);
            })
            ->when($request->prodi_id, function ($q, $s) {
                $q->where('prodi_id', $s);
            })
            ->when($request->periode_id, function ($q, $s) {
                $q->where('periode_id', $s);
            })
            ->orderByRaw("CASE severity WHEN 'kritis' THEN 0 WHEN 'berat' THEN 1 WHEN 'sedang' THEN 2 WHEN 'ringan' THEN 3 ELSE 4 END ASC")
            ->orderBy('created_at', 'DESC')
            ->paginate((int) $request->per_page ?: 10);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $audit,
            ]);
        }

        return Inertia::render('Spmi/Audit/Index', [
            'audit' => $audit,
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
            'standar_mutu_list' => StandarMutu::select('id', 'kode_standar', 'nama_standar')->get(),
            'user_list' => User::select('id', 'name')->get(),
            'filters' => $request->only(['search', 'status', 'standar_mutu_id', 'severity', 'pic_user_id', 'prodi_id', 'periode_id']),
        ]);
    }

    /**
     * Store a newly created audit mutu.
     */
    public function store(AuditMutuRequest $request)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($request, $validated) {
            // Handle file upload
            if ($request->hasFile('evidence_file')) {
                $path = $request->file('evidence_file')->store('audit-evidence', 'public');
                $validated['evidence_file'] = $path;
            }

            // Set default status for new audit
            $validated['status'] = 'draft';

            $auditMutu = AuditMutu::create($validated);

            // Log creation to audit history
            AuditHistory::create([
                'audit_mutu_id' => $auditMutu->id,
                'user_id' => auth()->id(),
                'field' => 'created',
                'old_value' => null,
                'new_value' => json_encode($auditMutu->toArray()),
                'action' => 'audit_created',
            ]);

            // Auto-create CAPA if severity >= sedang
            $severityOrder = ['ringan' => 1, 'sedang' => 2, 'berat' => 3, 'kritis' => 4];
            $severityLevel = $severityOrder[$auditMutu->severity ?? 'ringan'] ?? 1;
            if ($severityLevel >= 2) {
                try {
                    $this->capaService->createFromAudit($auditMutu);
                    Log::info('CAPA auto-created from audit', [
                        'audit_id' => $auditMutu->id,
                        'severity' => $auditMutu->severity,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to auto-create CAPA', [
                        'audit_id' => $auditMutu->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Dispatch event for kritis findings
            if ($auditMutu->severity === 'kritis') {
                try {
                    \App\Events\AuditSevereFindingCreated::dispatch($auditMutu);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch AuditSevereFindingCreated event', [
                        'audit_id' => $auditMutu->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Audit mutu created', [
                'audit_id' => $auditMutu->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Audit mutu berhasil ditambahkan.');
        });
    }

    /**
     * Update the specified audit mutu.
     */
    public function update(AuditMutuRequest $request, AuditMutu $auditMutu)
    {
        // Check lock
        if ($auditMutu->is_locked) {
            abort(403, 'Audit mutu terkunci dan tidak dapat diubah.');
        }

        $validated = $request->validated();

        return DB::transaction(function () use ($request, $auditMutu, $validated) {
            // Handle file upload
            if ($request->hasFile('evidence_file')) {
                // Delete old file if exists
                if ($auditMutu->evidence_file) {
                    Storage::disk('public')->delete($auditMutu->evidence_file);
                }
                $path = $request->file('evidence_file')->store('audit-evidence', 'public');
                $validated['evidence_file'] = $path;
            }

            // Track field-level changes for audit history
            $changes = [];
            foreach ($validated as $field => $newValue) {
                $oldValue = $auditMutu->{$field};
                if ($oldValue != $newValue) {
                    $changes[] = [
                        'audit_mutu_id' => $auditMutu->id,
                        'user_id' => auth()->id(),
                        'field' => $field,
                        'old_value' => (string) $oldValue,
                        'new_value' => (string) $newValue,
                        'action' => 'field_updated',
                    ];
                }
            }

            $auditMutu->update($validated);

            // Batch insert audit history for field changes
            if (! empty($changes)) {
                AuditHistory::insert($changes);
            }

            Log::info('Audit mutu updated', [
                'audit_id' => $auditMutu->id,
                'user_id' => auth()->id(),
                'changed_fields' => array_keys($validated),
            ]);

            return redirect()->back()->with('success', 'Audit mutu berhasil diperbarui.');
        });
    }

    /**
     * Transition audit to a new status via workflow service.
     */
    public function transition(Request $request, AuditMutu $auditMutu)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:draft,submitted,assigned,in_progress,awaiting_verification,verified,closed,archived,rejected',
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            $this->workflowService->transition(
                $auditMutu,
                $validated['status'],
                auth()->id(),
                $validated['note'] ?? null
            );

            Log::info('Audit status transitioned', [
                'audit_id' => $auditMutu->id,
                'from' => $auditMutu->status,
                'to' => $validated['status'],
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Status audit berhasil diubah.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors(['status' => $e->getMessage()]);
        }
    }

    /**
     * Batch transition multiple audits to a new status.
     */
    public function batchTransition(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:trx_audit_mutu,id',
            'status' => 'required|string|in:draft,submitted,assigned,in_progress,awaiting_verification,verified,closed,archived,rejected',
            'note' => 'nullable|string|max:1000',
        ]);

        $successCount = 0;
        $errors = [];

        DB::transaction(function () use ($validated, &$successCount, &$errors) {
            $audits = AuditMutu::whereIn('id', $validated['ids'])->lockForUpdate()->get();

            foreach ($audits as $audit) {
                try {
                    $this->workflowService->transition(
                        $audit,
                        $validated['status'],
                        auth()->id(),
                        $validated['note'] ?? null
                    );
                    $successCount++;
                } catch (\RuntimeException $e) {
                    $errors[] = "Audit #{$audit->id}: {$e->getMessage()}";
                }
            }
        });

        $message = "{$successCount} audit berhasil diubah statusnya.";
        if (! empty($errors)) {
            $message .= ' Gagal: ' . implode(', ', $errors);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Display the specified audit mutu with full relations.
     */
    public function show(AuditMutu $auditMutu): Response|JsonResponse
    {
        $auditMutu->load([
            'prodi', 'periode', 'standarMutu', 'picUser', 'auditor', 'verifiedByUser',
            'capas', 'histories.user',
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $auditMutu,
            ]);
        }

        return Inertia::render('Spmi/Audit/Detail', [
            'audit' => $auditMutu,
            'histories' => $auditMutu->histories,
            'can_transition' => [
                'edit' => !$auditMutu->is_locked && auth()->user()->can('audit-mutu.edit'),
                'delete' => !$auditMutu->is_locked && auth()->user()->can('audit-mutu.delete'),
                'verify' => auth()->user()->can('audit-mutu.verify'),
                'transition' => auth()->user()->can('audit-mutu.transition'),
            ],
        ]);
    }

    /**
     * AI-powered resolve to generate suggestions for an audit finding.
     * Also auto-classifies the finding to the most relevant standar mutu via RAG + keyword fallback.
     */
    public function aiResolve(AuditMutu $auditMutu)
    {
        try {
            $ragService = app(\App\Services\AI\RAGService::class);
            $question = "Apa rekomendasi untuk temuan audit: {$auditMutu->judul_audit}. Detail temuan: {$auditMutu->temuan}";
            $result = $ragService->ask($question);

            // Classify standar from the finding text
            $classification = $this->classifyStandarFromText($auditMutu->temuan ?? $auditMutu->judul_audit);

            // Auto-assign standar_mutu if confidence is high enough and not already set
            if ($classification['standar_id'] !== null && $classification['confidence'] >= 0.5 && ! $auditMutu->standar_mutu_id) {
                $auditMutu->update(['standar_mutu_id' => $classification['standar_id']]);
                AuditHistory::log(
                    action: 'standar_mutu_assigned',
                    entity: $auditMutu,
                    field: 'standar_mutu_id',
                    oldValue: null,
                    newValue: $classification['kode_standar'] . ' — ' . $classification['nama_standar'],
                );
            }

            return response()->json([
                'success' => true,
                'suggestion' => $result['answer'] ?? 'Tidak ada saran yang tersedia.',
                'sources' => $result['sources'] ?? [],
                'classification' => $classification,
            ]);
        } catch (\Exception $e) {
            Log::error('AI resolve failed for audit', [
                'audit_id' => $auditMutu->id,
                'error' => $e->getMessage(),
            ]);

            // Fallback: classify + keyword-based suggestions
            $classification = $this->classifyStandarFromText($auditMutu->temuan ?? $auditMutu->judul_audit);

            // Auto-assign standar_mutu if not already set
            if ($classification['standar_id'] !== null && $classification['confidence'] >= 0.5 && ! $auditMutu->standar_mutu_id) {
                $auditMutu->update(['standar_mutu_id' => $classification['standar_id']]);
            }

            $temuan = strtolower($auditMutu->temuan ?? '');
            $saran = 'Berdasarkan analisis AI: ';

            if (str_contains($temuan, 'kurikulum') || str_contains($temuan, 'rps')) {
                $saran .= 'Lakukan revisi RPS dan pemutakhiran kurikulum sesuai standar OBE. Koordinasikan dengan KBK untuk pemetaan CPL yang lebih akurat.';
            } elseif (str_contains($temuan, 'sdm') || str_contains($temuan, 'dosen')) {
                $saran .= 'Tingkatkan rasio dosen dan mahasiswa melalui rekrutmen atau tugas belajar. Dorong dosen untuk meningkatkan sertifikasi kompetensi industri.';
            } elseif (str_contains($temuan, 'sarana') || str_contains($temuan, 'fasilitas')) {
                $saran .= 'Segera ajukan pengadaan inventaris pendukung laboratorium dan perbaikan fasilitas ruang kelas untuk mendukung kenyamanan belajar.';
            } else {
                $saran .= 'Lakukan koordinasi dengan unit terkait untuk menindaklanjuti temuan ini sesuai dengan standar SPMI yang berlaku. Buat timeline perbaikan dalam 3 bulan kedepan.';
            }

            return response()->json([
                'success' => true,
                'suggestion' => $saran,
                'classification' => $classification,
            ]);
        }
    }

    /**
     * Classify audit finding text to the most relevant standar mutu.
     * Uses RAG-based AI first, falls back to keyword matching.
     */
    private function classifyStandarFromText(string $text): array
    {
        // Try RAG first
        try {
            $ragService = app(\App\Services\AI\RAGService::class);
            $result = $ragService->ask(
                question: "Klasifikasikan temuan audit mutu berikut ke standar mutu yang paling relevan. " .
                          "Temuan: \"{$text}\". " .
                          "Kembalikan hanya kode standar (STD-XXX) dan nama standar.",
                categoryId: null,
                topK: 5
            );

            if ($result && isset($result['answer'])) {
                // Parse answer to extract STD-XXX code
                preg_match('/STD-\d{3}/', $result['answer'], $matches);
                if (! empty($matches)) {
                    $standar = StandarMutu::where('kode_standar', $matches[0])->first();
                    if ($standar) {
                        return [
                            'standar_id' => $standar->id,
                            'kode_standar' => $standar->kode_standar,
                            'nama_standar' => $standar->nama_standar,
                            'confidence' => 0.85,
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('RAG classification failed, falling back to keyword matching: ' . $e->getMessage());
        }

        // Fallback: keyword matching
        $keywords = [
            'STD-001' => ['kompetensi lulusan', 'cpl', 'capaian pembelajaran', 'profil lulusan'],
            'STD-002' => ['isi pembelajaran', 'kurikulum', 'mata kuliah', 'bahan ajar'],
            'STD-003' => ['proses pembelajaran', 'perkuliahan', 'metode pembelajaran', 'blended learning'],
            'STD-004' => ['penilaian', 'evaluasi pembelajaran', 'ujian', 'nilai', 'asesmen'],
            'STD-005' => ['dosen', 'tenaga kependidikan', 'kualifikasi', 'sertifikasi dosen', 'rasio dosen'],
            'STD-006' => ['sarana', 'prasarana', 'ruang kelas', 'laboratorium', 'perpustakaan'],
            'STD-007' => ['pengelolaan', 'tata pamong', 'organisasi', 'manajemen', 'sistem informasi'],
            'STD-008' => ['pembiayaan', 'biaya kuliah', 'dana', 'anggaran', 'keuangan'],
            'STD-009' => ['hasil penelitian', 'publikasi', 'haki', 'paten', 'buku'],
            'STD-010' => ['isi penelitian', 'roadmap penelitian', 'rencana induk penelitian'],
            'STD-011' => ['proses penelitian', 'metodologi', 'etik penelitian'],
            'STD-012' => ['penilaian penelitian', 'review penelitian'],
            'STD-013' => ['peneliti', 'kapasitas peneliti'],
            'STD-014' => ['sarana penelitian', 'laboratorium penelitian'],
            'STD-015' => ['pengelolaan penelitian', 'pusat penelitian', 'lembaga penelitian'],
            'STD-016' => ['pendanaan penelitian', 'dana penelitian', 'hibah'],
            'STD-025' => ['visi', 'misi', 'tujuan', 'strategi', 'renstra'],
            'STD-026' => ['tata pamong', 'kerjasama', 'moa', 'mou', 'jejaring'],
            'STD-027' => ['kemahasiswaan', 'ukm', 'beasiswa', 'bimbingan konseling'],
            'STD-028' => ['sumber daya manusia', 'sdm', 'dosen tetap', 'tenaga kependidikan'],
            'STD-034' => ['jaminan mutu', 'spmi', 'audit mutu', 'gugus mutu', 'lpm'],
            'STD-035' => ['sistem informasi', 'sim', 'tugas akhir', 'sistem akademik'],
            'STD-036' => ['audit mutu internal', 'ami', 'temuan audit', 'auditor'],
        ];

        $textLower = strtolower($text);
        $bestMatch = null;
        $bestScore = 0;

        foreach ($keywords as $code => $kwList) {
            $score = 0;
            foreach ($kwList as $kw) {
                if (str_contains($textLower, $kw)) {
                    $score++;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $code;
            }
        }

        if ($bestMatch) {
            $standar = StandarMutu::where('kode_standar', $bestMatch)->first();
            if ($standar) {
                return [
                    'standar_id' => $standar->id,
                    'kode_standar' => $standar->kode_standar,
                    'nama_standar' => $standar->nama_standar,
                    'confidence' => $bestScore > 2 ? 0.7 : 0.4,
                ];
            }
        }

        return ['standar_id' => null, 'kode_standar' => null, 'nama_standar' => null, 'confidence' => 0];
    }

    /**
     * Remove the specified audit mutu (soft delete).
     */
    public function destroy(AuditMutu $auditMutu)
    {
        if ($auditMutu->is_locked) {
            abort(403, 'Audit mutu terkunci dan tidak dapat dihapus.');
        }

        $auditMutu->delete();

        Log::info('Audit mutu deleted', [
            'audit_id' => $auditMutu->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Audit mutu berhasil dihapus.');
    }
}
