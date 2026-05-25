# RENCANA IMPLEMENTASI SPMI LENGKAP
## Sistem Multi-Agent AI Akreditasi — ITSNU Pekalongan

---

## 📋 DAFTAR ISI

1. [Arsitektur Sistem](#1-arsitektur-sistem)
2. [Database Schema]($2-database-schema-bar)
3. [Backend: Services & Controllers](#3-backend-services--controllers)
4. [Frontend: Halaman & Komponen](#4-frontend-halaman--komponen)
5. [AI Integration](#5-ai-integration)
6. [Dashboard SPMI](#6-dashboard-spmi)
7. [Anti-Bug Patterns](#7-anti-bug-patterns)
8. [Database Sync & Integrasi](#8-database-sync--integrasi)
9. [Sprint Breakdown](#9-sprint-breakdown)

---

## 1. ARSITEKTUR SISTEM

### 1.1 Arsitektur Modul SPMI (Target)

```
┌──────────────────────────────────────────────────────────────────────┐
│                       SPMI DASHBOARD                                │
│  (Tren temuan, risk heatmap, close rate, skor mutu, early warning)  │
│          ┌──────────────┐  ┌─────────────────┐  ┌────────────┐      │
│          │  PPEPP        │  │  Standar Mutu   │  │  Dokumen   │      │
│          │  Cycle Engine │◀─│  (SNDIKTI/SPMI) │◀─│  Mutu      │      │
│          └──────┬───────┘  └─────────────────┘  └────────────┘      │
│                 ▼                                                    │
│  ┌────────────────────────┐  ┌────────────────────────────┐         │
│  │    AUDIT MUTU (AMI)     │  │      CAPA SYSTEM           │         │
│  │  ┌──────────────────┐  │  │  ┌──────────────────────┐  │         │
│  │  │ Temuan           │──┼──┼─▶│ Root Cause Analysis  │  │         │
│  │  │ Severity         │  │  │  │ Corrective Action    │  │         │
│  │  │ Standar Mutu     │  │  │  │ Preventive Action    │  │         │
│  │  │ Status Workflow  │  │  │  │ PIC + Deadline       │  │         │
│  │  │ PIC Assignment   │  │  │  │ Evidence Upload      │  │         │
│  │  └──────────────────┘  │  │  └──────────────────────┘  │         │
│  └────────────────────────┘  └────────────────────────────┘         │
│           │                            │                            │
│           ▼                            ▼                            │
│  ┌──────────────────────────────────────────────────────┐           │
│  │                   INTEGRATION LAYER                   │           │
│  │  Risk Register  │  IKU/Cascading  │  RKAT  │  AI Agent│           │
│  │  Peringatan     │  Knowledge Base │  EDPS  │  Survey   │           │
│  └──────────────────────────────────────────────────────┘           │
└──────────────────────────────────────────────────────────────────────┘
```

### 1.2 Layer Arsitektur

```
┌─────────────────────────────────────────────────────────────────┐
│                     FRONTEND (React/Inertia)                     │
│  Pages/Spmi/  +  Components/SPMI/  +  Layouts/AuthenticatedLayout │
├─────────────────────────────────────────────────────────────────┤
│                   API ROUTES (routes/web.php)                    │
├───────────────────┬─────────────────────────────────────────────┤
│  Controllers      │  Services                                   │
│  Api/             │  Services/SPMI/                              │
│  └ SpmiController │  └ AuditAnalysisService                     │
│  └ StandarController│  └ CapaService                            │
│  └ AuditController │  └ SpmiWorkflowService                     │
│  └ CapaController │  └ EdpsService                              │
│  └ EdpsController │  └ SpmiReportService (pelaporan LLDIKTI)    │
│  └ RtmController  │  └ RtmService                               │
│  └ SpmiDashboard  │  └ SpmiDashboardService                     │
├───────────────────┴─────────────────────────────────────────────┤
│                       MODELS (Eloquent)                          │
│  StandarMutu │ AuditMutu │ Capa │ Edps │ Rtm │ SpmiCycle        │
│  AuditHistory │ SpmiDocument │ SurveyResponse                    │
├─────────────────────────────────────────────────────────────────┤
│                   DATABASE (PostgreSQL)                          │
│  migrations/ + seeds/ + factories/                               │
├─────────────────────────────────────────────────────────────────┤
│              INTEGRATION (Existing Infrastructure)               │
│  MCPClientService  →  Python AI Agents (peringatan, prediksi)   │
│  RAGService         →  Knowledge Base (vector search)            │
│  PeringatanSystem   →  auto-reminder & escalation               │
│  RiskRegister       →  temuan kritis → risk                     │
│  IKU/RKAT           →  temuan → cascading → anggaran            │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. DATABASE SCHEMA BARU

### 2.1 Migration: `create_m_standar_mutu_table.php`

```php
Schema::create('m_standar_mutu', function (Blueprint $table) {
    $table->id();
    $table->string('kategori', 50);              // Pendidikan, Penelitian, PKM, Tambahan
    $table->string('kode_standar', 30)->unique(); // STD-001, STD-002...
    $table->string('nama_standar', 200);
    $table->text('deskripsi')->nullable();
    $table->string('sumber', 100)->nullable();     // SNDIKTI, SPMI institusi, ISO, AUN-QA
    $table->string('referensi_regulasi', 100)->nullable(); // Permendikbudristek 53/2023
    $table->decimal('target_nilai', 5, 2)->nullable(); // target minimal
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
});

// Standar Mutu seeder: 36 standar (8 Pendidikan + 8 Penelitian + 8 PKM + 12 Tambahan)
// Referensi: Tel-U 36 standar, BINUS quality standard
```

### 2.2 Migration: `create_trx_capa_table.php` (Corrective Action Preventive Action)

```php
Schema::create('trx_capa', function (Blueprint $table) {
    $table->id();
    $table->foreignId('audit_mutu_id')->constrained('trx_audit_mutu')->cascadeOnDelete();
    $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();

    // Root Cause Analysis
    $table->enum('root_cause_category', [
        'proses', 'sdm', 'sarana', 'keuangan', 'regulasi', 'eksternal', 'lainnya'
    ])->nullable();
    $table->text('root_cause_analysis')->nullable();

    // Corrective Action
    $table->text('corrective_action')->nullable();
    $table->date('corrective_deadline')->nullable();
    $table->date('corrective_completed_at')->nullable();
    $table->string('corrective_evidence_file', 255)->nullable();

    // Preventive Action
    $table->text('preventive_action')->nullable();
    $table->date('preventive_deadline')->nullable();
    $table->date('preventive_completed_at')->nullable();
    $table->string('preventive_evidence_file', 255)->nullable();

    // Workflow
    $table->enum('status', [
        'draft', 'open', 'in_progress', 'awaiting_verification',
        'verified', 'rejected', 'closed', 'archived'
    ])->default('open');

    $table->text('verification_note')->nullable();
    $table->timestamp('verified_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

### 2.3 Migration: `add_spmi_columns_to_trx_audit_mutu_table.php`

```php
Schema::table('trx_audit_mutu', function (Blueprint $table) {
    // Relasi ke standar mutu
    $table->foreignId('standar_mutu_id')->nullable()->constrained('m_standar_mutu')->nullOnDelete();

    // Severity
    $table->enum('severity', ['ringan', 'sedang', 'berat', 'kritis'])->default('ringan')->after('status');

    // Workflow status (enhance dari open/in_progress/closed)
    // Diubah: status VARCHAR(30) jadi ENUM yang lebih detail
    // ALTER TABLE trx_audit_mutu ALTER COLUMN status TYPE VARCHAR(30);

    // PIC assignment
    $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('auditor_user_id')->nullable()->constrained('users')->nullOnDelete();

    // Timeline
    $table->date('deadline_tindak_lanjut')->nullable();
    $table->timestamp('closed_at')->nullable();

    // Evidence & verification
    $table->string('evidence_file', 255)->nullable();
    $table->text('verification_note')->nullable();
    $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('verified_at')->nullable();

    // Immutability
    $table->boolean('is_locked')->default(false); // tidak bisa diedit setelah verified
    $table->timestamp('locked_at')->nullable();
});
```

### 2.4 Migration: `create_trx_audit_mutu_histories_table.php` (Audit Trail)

```php
Schema::create('trx_audit_mutu_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('audit_mutu_id')->constrained('trx_audit_mutu')->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('field', 100);        // status, severity, temuan, pic_user_id, dll
    $table->text('old_value')->nullable();
    $table->text('new_value')->nullable();
    $table->string('action', 50);        // created, updated, status_changed, verified, locked
    $table->timestamps();

    $table->index('audit_mutu_id');
    $table->index('user_id');
    $table->index('created_at');
});
```

### 2.5 Migration: `create_m_spmi_dokumen_table.php` (Dokumen Mutu)

```php
Schema::create('m_spmi_dokumen', function (Blueprint $table) {
    $table->id();
    $table->string('kategori', 50);       // Kebijakan, Manual, Standar, SOP, Form, Laporan
    $table->string('nomor_dokumen', 50)->unique(); // SPMI-001, SPMI-SOP-001
    $table->string('judul', 200);
    $table->text('deskripsi')->nullable();
    $table->string('file_path', 255)->nullable();
    $table->string('file_original_name', 255)->nullable();
    $table->integer('version')->default(1);
    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('approved_at')->nullable();
    $table->date('tanggal_berlaku')->nullable();
    $table->date('tanggal_kadaluarsa')->nullable();
    $table->enum('status', ['draft', 'review', 'approved', 'expired', 'archived'])->default('draft');
    $table->text('catatan_revisi')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

### 2.6 Migration: `create_trx_survey_spmi_table.php` (Survey Stakeholder)

```php
Schema::create('trx_survey_spmi', function (Blueprint $table) {
    $table->id();
    $table->foreignId('periode_id')->constrained('m_periode_akademik');
    $table->enum('responden_type', ['mahasiswa', 'dosen', 'alumni', 'pengguna_lulusan']);
    $table->json('responses');  // [{kode_pertanyaan: string, skor: int}]
    $table->decimal('skor_rata_rata', 5, 2)->nullable();
    $table->string('token', 64)->unique()->nullable(); // untuk akses sekali pakai
    $table->timestamp('diisi_at')->nullable();
    $table->timestamps();
});
```

### 2.7 Migration: `create_trx_edps_table.php` (Evaluasi Diri Program Studi)

```php
Schema::create('trx_edps', function (Blueprint $table) {
    $table->id();
    $table->foreignId('prodi_id')->constrained('m_prodi');
    $table->foreignId('periode_id')->constrained('m_periode_akademik');
    $table->foreignId('standar_mutu_id')->constrained('m_standar_mutu');
    $table->decimal('target', 5, 2);
    $table->decimal('capaian', 5, 2)->nullable();
    $table->text('analisis')->nullable();
    $table->string('bukti_file', 255)->nullable();
    $table->enum('status', ['draft', 'submitted', 'reviewed'])->default('draft');
    $table->timestamps();

    $table->unique(['prodi_id', 'periode_id', 'standar_mutu_id']);
});
```

### 2.8 Migration: `create_trx_rtm_table.php` (Rapat Tinjauan Manajemen)

```php
Schema::create('trx_rtm', function (Blueprint $table) {
    $table->id();
    $table->string('judul', 200);
    $table->date('tanggal_rapat');
    $table->text('agenda')->nullable();
    $table->text('notulen')->nullable();
    $table->string('file_notulen', 255)->nullable();
    $table->foreignId('dipimpin_oleh')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
});

Schema::create('trx_rtm_action_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('rtm_id')->constrained('trx_rtm')->cascadeOnDelete();
    $table->text('deskripsi');
    $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->date('deadline')->nullable();
    $table->enum('status', ['open', 'in_progress', 'completed', 'cancelled'])->default('open');
    $table->text('hasil')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});
```

### 2.9 Migration: `seed_standar_mutu_and_spmi_cycles.php`

```php
// Seeder: 36 Standar Mutu (8 Pendidikan + 8 Penelitian + 8 PKM + 12 Tambahan)
// Seeder: Update SpmiCycle — tambah 5 tahap PPEPP lengkap untuk setiap prodi aktif
// Seeder: Dokumen mutu default (Kebijakan SPMI, Manual Mutu, dll)
```

---

## 3. BACKEND: SERVICES & CONTROLLERS

### 3.1 Service Layer Baru

#### `app/Services/SPMI/SpmiWorkflowService.php`

**Tanggung jawab**: State machine untuk workflow audit + CAPA.

```php
class SpmiWorkflowService {
    // State definitions
    const AUDIT_STATUS_FLOW = [
        'draft' => ['submitted'],
        'submitted' => ['assigned'],
        'assigned' => ['in_progress'],
        'in_progress' => ['awaiting_verification'],
        'awaiting_verification' => ['verified', 'rejected'],
        'verified' => ['closed'],
        'closed' => ['archived'],
        'rejected' => ['in_progress'], // bisa diperbaiki
        'archived' => [], // terminal state
    ];

    // Logic:
    // - transition(AuditMutu, toStatus, userId, note): void
    //   → validasi transisi valid
    //   → update status + timestamps
    //   → insert ke AuditHistory
    //   → trigger event: AuditStatusChanged
    //   → jika closed → hitung skor mutu prodi
    //   → jika target severity >= berat → auto-create RiskRegister
    //   → auto-create Peringatan jika deadline > 30 hari

    // - assignPIC(AuditMutu, userId): void
    // → set pic_user_id
    // → send Peringatan (info) ke PIC

    // - canTransition(currentStatus, targetStatus): bool
    // → cek state machine

    // Anti-bug: DB transaction + lock row
    // Anti-bug: idempotency — jika status sudah sama, skip
    // Anti-bug: validasi user permission di setiap transisi
}
```

#### `app/Services/SPMI/AuditAnalysisService.php`

**Tanggung jawab**: Agregasi data audit untuk dashboard, scoring, early warning.

```php
class AuditAnalysisService {
    // getProdiScore(prodiId, periodeId): array
    // → hitung skor mutu berdasarkan: severity × jumlah temuan × bobot standar
    // → rumus: score = max(100 - sum(severity_weight × count × std_weight), 0)
    // → severity weight: ringan=2, sedang=5, berat=15, kritis=30

    // getTrend(prodiId, tahunRange): array
    // → tren temuan per bulan/kuartal
    // → tren close rate
    // → tren average resolution time

    // getRiskHeatmap(prodiId, periodeId): array
    // → matrix severity × standar_mutu_category
    // → warna: hijau (<5 temuan ringan), kuning, merah, hitam (kritis)

    // getEarlyWarning(): array
    // → prodi dengan temuan kritis > threshold
    // → temuan tanpa CAPA > 14 hari
    // → deadline mendekat (< 7 hari)

    // Anti-bug: cache hasil agregasi (cache key: prodi+periode, TTL: 5 menit)
    // Anti-bug: graceful degradation — jika data kurang, return partial
    // Anti-bug: query optimization — eager loading + index
}
```

#### `app/Services/SPMI/CapaService.php`

**Tanggung jawab**: CRUD + workflow CAPA.

```php
class CapaService {
    // createFromAudit(AuditMutu): Capa
    // → auto-buat CAPA untuk temuan severity >= sedang
    // → copy temuan + rekomendasi ke root_cause_analysis

    // submitForVerification(Capa, userId): void
    // → validasi: corrective_action harus diisi, evidence harus ada
    // → set status = 'awaiting_verification'

    // verify(Capa, userId, note, approved): void
    // → jika approved: set verified_by, verified_at, status='verified'
    // → auto-lock AuditMutu terkait (is_locked=true)
    // → jika rejected: status balik ke 'in_progress' + catatan revisi

    // getOverdue(): Collection
    // → CAPA dengan deadline lewat tapi belum selesai
}
```

#### `app/Services/SPMI/SpmiDashboardService.php`

**Tanggung jawab**: Data aggregation untuk dashboard SPMI.

```php
class SpmiDashboardService {
    // getOverview(prodiId, periodeId): array
    // → total_temuan, open, in_progress, closed
    // → rata_rata_resolution_time (hari)
    // → close_rate %
    // → skor_mutu
    // → capa_terlambat_count

    // getChartData(prodiId, periodeId): array
    // → temuan_per_standar: [{standar, count, severity_avg}]
    // → temuan_per_bulan: [{bulan, count}]
    // → severity_distribution: [{severity, count}]

    // getProdiRanking(periodeId): Collection
    // → ranking prodi berdasarkan skor mutu
    // → digunakan Rektor/Dekan/LPM

    // Anti-bug: semua method pake read-only DB transaction
    // Anti-bug: handle prodiId = null (untuk level institusi)
}
```

#### `app/Services/SPMI/SpmiReportService.php`

**Tanggung jawab**: Generate laporan SPMI untuk LLDIKTI.

```php
class SpmiReportService {
    // generateLaporanSpmi(periodeId): array
    // → format sesuai Permendiktisaintek 39/2025
    // → data: standar mutu, capaian, temuan, CAPA, survey, EDPS
    // → return structured array ready for PDF/Excel

    // exportToExcel(periodeId): string (file path)
    // → export semua data SPMI ke format Excel LLDIKTI

    // getPelaporanPddikti(): array
    // → data yang perlu disinkronkan ke PD Dikti
}
```

### 3.2 Enhanced AuditMutuController

```php
class AuditMutuController extends Controller {
    public function __construct(
        private SpmiWorkflowService $workflowService,
        private AuditAnalysisService $analysisService,
        private CapaService $capaService,
    ) {}

    // OVERVIEW — ini adalah halaman utama yang diminta user
    public function index(Request $request) {
        // Filter: search, status, standar_mutu_id, severity, prodi_id, periode_id
        // Dengan: with('prodi', 'periode', 'standarMutu', 'picUser', 'capa')
        // Sorting: severity DESC, created_at DESC
        // Pagination: 10

        // Kirim data ke dashboard:
        // - audit_items: PaginatedData<AuditMutu>
        // - filter_options: standar_list, prodi_list, periode_list
        // - dashboard_stats: dari SpmiDashboardService
        // - spmi_cycles: PPEPP cycles
    }

    public function store(AuditMutuRequest $request) {
        DB::transaction(function () use ($request) {
            $audit = AuditMutu::create($request->validated());

            // Log history
            AuditHistory::log('created', $audit, null, auth()->id());

            // Jika severity >= sedang → auto-create CAPA
            if (in_array($audit->severity, ['sedang', 'berat', 'kritis'])) {
                $this->capaService->createFromAudit($audit);
            }

            // Integrasi: jika severity kritis → trigger Risk Register
            if ($audit->severity === 'kritis') {
                event(new AuditSevereFindingCreated($audit));
            }
        });
    }

    public function update(AuditMutuRequest $request, AuditMutu $auditMutu) {
        // Anti-bug: cek is_locked — tolak jika sudah diverifikasi
        if ($auditMutu->is_locked) {
            return back()->withErrors(['error' => 'Temuan sudah diverifikasi dan tidak bisa diedit.']);
        }

        DB::transaction(function () use ($request, $auditMutu) {
            $old = $auditMutu->toArray();
            $auditMutu->update($request->validated());

            // Log setiap perubahan field
            foreach ($request->validated() as $field => $newValue) {
                if ($old[$field] != $newValue) {
                    AuditHistory::log('updated', $auditMutu, $field, auth()->id(), $old[$field], $newValue);
                }
            }
        });
    }

    public function transition(Request $request, AuditMutu $auditMutu) {
        $request->validate(['status' => 'required|string', 'note' => 'nullable|string']);

        DB::transaction(function () use ($request, $auditMutu) {
            $this->workflowService->transition($auditMutu, $request->status, auth()->id(), $request->note);
        });

        return redirect()->back()->with('success', 'Status berhasil diperbarui.');
    }

    public function aiResolve(AuditMutu $auditMutu) {
        // ✅ REAL AI — bukan str_contains()
        $ragService = app(RAGService::class);

        // 1. Cari dokumen relevan dari Knowledge Base
        $kbResults = $ragService->ask(
            question: "Rekomendasi untuk temuan audit: {$auditMutu->temuan}. " .
                      "Berdasarkan standar {$auditMutu->standarMutu?->nama_standar}. " .
                      "Berikan saran tindak lanjut yang spesifik dan terukur.",
            categoryId: null,
            topK: 3
        );

        // 2. Deteksi standar mutu otomatis dari teks temuan
        $detectedStandar = $this->classifyStandarFromText($auditMutu->temuan);

        // 3. Format output
        return response()->json([
            'suggestion' => $kbResults['answer'] ?? 'Tidak dapat menghasilkan rekomendasi.',
            'sources' => $kbResults['sources'] ?? [],
            'detected_standar' => $detectedStandar,
            'confidence' => $detectedStandar['confidence'] ?? 0,
        ]);
    }

    private function classifyStandarFromText(string $text): array {
        // Implementasi: pakai RAG untuk klasifikasi
        // Fallback: keyword matching sederhana
        // → map keywords ke kode standar
        // → return {standar_id, kode_standar, confidence}
    }
}
```

### 3.3 New Controllers

#### `app/Http/Controllers/Api/SpmiCycleController.php`

```php
class SpmiCycleController extends Controller {
    // index(): list PPEPP cycles — filter by prodi, periode
    // store(): create new cycle
    // update(): edit cycle (progress, status, catatan)
    // destroy(): soft delete

    // UI: Manajemen siklus PPEPP — admin LPM bisa:
    // 1. Buka siklus baru: Penetapan → input standar + target
    // 2. Pelaksanaan → prodi upload evidence
    // 3. Evaluasi → trigger AMI (link ke AuditMutu)
    // 4. Pengendalian → CAPA tracking
    // 5. Peningkatan → review + naikkan standar
}
```

#### `app/Http/Controllers/Api/CapaController.php`

```php
class CapaController extends Controller {
    // index(): list CAPA — filter by prodi, status, PIC, overdue
    // show(): detail CAPA dengan timeline
    // update(): edit CAPA (RCA, corrective, preventive, upload evidence)
    // submitVerification(): request verification
    // verify(): approve/reject (hanya LPM/auditor)
    // getTimeline(): history CAPA dalam bentuk timeline
}
```

#### `app/Http/Controllers/Api/SpmiDashboardController.php`

```php
class SpmiDashboardController extends Controller {
    public function __construct(private SpmiDashboardService $dashboardService) {}

    public function overview(Request $request) {
        // Stats cards: total temuan, open, in_progress, closed
        // Charts: trend, severity distribution, top issues
        // Early warning: overdue CAPA, severity kritis
        // PPEPP cycle progress
        // Ranking prodi
    }

    public function chartData(Request $request) {
        return response()->json(
            $this->dashboardService->getChartData(
                $request->prodi_id, $request->periode_id
            )
        );
    }
}
```

#### `app/Http/Controllers/Api/StandarMutuController.php`

```php
class StandarMutuController extends Controller {
    // index(): list standar mutu dengan filter kategori
    // store(): create standar
    // update(): edit standar
    // destroy(): soft delete (hanya jika tidak ada temuan terkait)
}
```

### 3.4 Events & Listeners

```php
// Events:
class AuditStatusChanged {
    public function __construct(
        public AuditMutu $audit,
        public string $oldStatus,
        public string $newStatus,
        public User $user,
    ) {}
}

class AuditSevereFindingCreated {
    public function __construct(public AuditMutu $audit) {}
}

class CapaDeadlineApproaching {
    public function __construct(public Capa $capa) {}
}

// Listeners:
class CreateRiskRegisterFromSevereFinding {
    public function handle(AuditSevereFindingCreated $event): void {
        RiskRegister::create([
            'prodi_id' => $event->audit->prodi_id,
            'periode_id' => $event->audit->periode_id,
            'nama_risiko' => "Temuan audit kritis: {$event->audit->judul_audit}",
            'kategori' => 'mutu',
            'dampak' => 'tinggi',
            'probabilitas' => 'tinggi',
            'skor_risiko' => '20',
            'mitigasi' => $event->audit->rekomendasi,
            'status' => 'open',
            'penanggung_jawab' => $event->audit->pic_user_id,
        ]);
    }
}

class SendPeringatanOnAuditAssignment {
    public function handle(AuditStatusChanged $event): void {
        if ($event->newStatus === 'assigned' && $event->audit->pic_user_id) {
            AgentPeringatanLog::create([
                'prodi_id' => $event->audit->prodi_id,
                'dosen_id' => $event->audit->pic_user_id,
                'jenis_peringatan' => 'tindak_lanjut_audit',
                'tingkat' => 'warning',
                'pesan' => "Anda ditugaskan untuk menindaklanjuti temuan audit: {$event->audit->judul_audit}",
                'is_read' => false,
            ]);
        }
    }
}

class CheckCapaDeadline extends Job {
    // Scheduled task (daily): cek CAPA dengan deadline < 7 hari
    // → auto-create Peringatan ke PIC
}
```

### 3.5 Form Requests (Validation)

```php
class AuditMutuRequest extends FormRequest {
    public function rules(): array {
        $rules = [
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'judul_audit' => 'required|string|max:200',
            'tanggal_audit' => 'required|date',
            'auditor' => 'nullable|string|max:200',
            'temuan' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
            'standar_mutu_id' => 'nullable|exists:m_standar_mutu,id',
            'severity' => 'required|in:ringan,sedang,berat,kritis',
        ];

        if ($this->isMethod('PUT')) {
            $rules['tindak_lanjut'] = 'nullable|string';
            $rules['status'] = 'nullable|string';
            $rules['evidence_file'] = 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:10240';
        }

        return $rules;
    }

    // Anti-bug: after validation, sanitize HTML tags from text fields
    // Anti-bug: strip XSS from temuan, rekomendasi, tindak_lanjut
}
```

---

## 4. FRONTEND: HALAMAN & KOMPONEN

### 4.1 Struktur Halaman Baru

```
resources/js/Pages/Spmi/
├── Dashboard.tsx              ← HALAMAN UTAMA (pengganti Index.tsx)
├── Audit/
│   ├── Index.tsx              ← Enhanced CRUD dengan workflow
│   ├── Detail.tsx             ← Detail audit + timeline + CAPA
│   └── Partials/
│       ├── AuditTable.tsx     ← Table komponen
│       ├── AuditFormModal.tsx ← Form create/edit
│       ├── AuditTimeline.tsx  ← Timeline komponen
│       ├── WorkflowActions.tsx← Tombol transisi status
│       └── SeverityBadge.tsx  ← Badge warna severity
├── Capa/
│   ├── Index.tsx              ← List CAPA
│   ├── Detail.tsx             ← Detail CAPA + form RCA + evidence upload
│   └── Partials/
│       └── CapaTimeline.tsx
├── StandarMutu/
│   └── Index.tsx              ← Manajemen standar mutu
├── DokumenMutu/
│   └── Index.tsx              ← Repository dokumen mutu
├── Cycle/
│   └── Index.tsx              ← Manajemen siklus PPEPP
├── Edps/
│   └── Index.tsx              ← Evaluasi Diri Program Studi
├── Rtm/
│   ├── Index.tsx              ← Rapat Tinjauan Manajemen
│   └── Detail.tsx
└── Survey/
    └── Index.tsx              ← Survey stakeholder
```

### 4.2 Halaman Utama: `Spmi/Dashboard.tsx` (Design Detail)

**Layout**:
- Header: "Dashboard Penjaminan Mutu SPMI"
- Breadcrumb: Dashboard > SPMI > Dashboard

**Filter Bar** (sticky top):
- Prodi dropdown (multi-select? single)
- Periode dropdown
- Tahun range (optional)

**Section 1: KPI Cards (Row 1)**
```
┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐
│ Total    │ │ Open     │ │ In       │ │ Close    │ │ Skor     │
│ Temuan   │ │ Temuan   │ │ Progress │ │ Rate     │ │ Mutu     │
│   42     │ │   12     │ │    8     │ │  76%     │ │  82.5    │
│ ── 15% ↑ │ │ ── 3% ↓  │ │ ── 5% ↑  │ │ ── 8% ↑  │ │ ── Baik  │
└──────────┘ └──────────┘ └──────────┘ └──────────┘ └──────────┘
```
- Ikon: AlertTriangle, Clock, RefreshCw, CheckCircle2, TrendingUp
- Warna: berdasarkan value (↑ hijau, ↓ merah)
- Link ke halaman terkait

**Section 2: PPEPP Cycle Progress (Row 2)**
```
┌────────────────────────────────────────────────────────────────┐
│  SIKLUS PPEPP                                                  │
│                                                                │
│  [P] Penetapan    ======████████████████████ 100% ✅           │
│  [P] Pelaksanaan  ======████████████████░░░░  80% ▶            │
│  [P] Evaluasi     ======██████████░░░░░░░░░░  45% ▶            │
│  [P] Pengendalian ======████░░░░░░░░░░░░░░░░  20% ▶            │
│  [P] Peningkatan  ======░░░░░░░░░░░░░░░░░░░░   0% ⏸            │
└────────────────────────────────────────────────────────────────┘
```
- Progress bar per tahap
- Tombol "Kelola Siklus" → link ke Spmi/Cycle

**Section 3: Charts (Row 3 - dua kolom)**
```
┌─────────────────────────────┐ ┌─────────────────────────────┐
│  TREN TEMUAN PER BULAN      │ │  SEVERITY DISTRIBUTION      │
│                             │ │                             │
│  📊 BarChart (Recharts)     │ │  📊 PieChart (Recharts)     │
│  Sumbu X: bulan (Jul-Jun)   │ │  Ringan ██ 15               │
│  Sumbu Y: jumlah temuan     │ │  Sedang ██ 12               │
│  Warna: biru (total)        │ │  Berat  ██ 3                │
│         merah (kritis)      │ │  Kritis █▓ 1                │
└─────────────────────────────┘ └─────────────────────────────┘
```

**Section 4: Early Warning & Overdue (Row 4)**
```
┌────────────────────────────────────────────────────────────────┐
│  ⚠ EARLY WARNING — PERHATIAN                                  │
│                                                                │
│  🟥 KRITIS: Prodi Informatika — 1 temuan kritis (7 hari lalu) │
│  🟡 OVERDUE: 3 CAPA lewat deadline (rata-rata 12 hari)        │
│  🟡 MENDEKAT: 5 temuan deadline < 7 hari                      │
│  ℹ️ BELUM DITUGASKAN: 2 temuan tanpa PIC                      │
└────────────────────────────────────────────────────────────────┘
```
- Auto-refresh setiap 60 detik (pake polling)
- Warna kritis: bg-red-50 border-red-500
- Warna overdue: bg-yellow-50 border-yellow-500

**Section 5: Standar Mutu & Temuan (Row 5)**
```
┌────────────────────────────────────────────────────────────────┐
│  TEMUAN PER STANDAR MUTU                                       │
│                                                                │
│  Standar Pendidikan      ████████████████    4 temuan (2 berat)│
│  Standar Penelitian      ████████████████    3 temuan (1 berat)│
│  Standar PKM             ██████              1 temuan (ringan) │
│  Standar Tambahan        ████████████████   10 temuan (3 berat)│
│  ...                                                            │
└────────────────────────────────────────────────────────────────┘
```
- Horizontal bar chart
- Warna bar: merah (jika ada severity kritis), kuning (berat), hijau (semua ringan)

### 4.3 Halaman Audit: `Spmi/Audit/Index.tsx` (Enhanced)

**Perubahan dari existing**:

1. **Filter bar** tambahan:
   - Dropdown Standar Mutu
   - Dropdown Severity
   - Dropdown PIC
   - Dropdown Status (workflow: draft/submitted/.../archived)
   - Tombol "Tambah Temuan"

2. **Table** tambahan kolom:
   - Kode Standar Mutu (badge)
   - Severity (badge warna)
   - PIC (nama user)
   - Deadline (dengan countdown)
   - CAPA Status (linked)

3. **Row styling**:
   - Severity kritis: bg-red-50
   - Severity berat: bg-orange-50
   - Overdue deadline: text-red-600

4. **Workflow Actions** (per row):
   - Dropdown button: "Ubah Status →"
   - Opsi sesuai state machine (hanya yang valid)
   - Confirmation modal sebelum transisi

5. **Bulk actions** (checkbox per row):
   - Assign PIC massal
   - Ubah status massal

### 4.4 Halaman CAPA: `Spmi/Capa/Index.tsx`

**Fitur**:
- Filter: prodi, status, PIC, severity
- Table: Judul Temuan, RCA, Corrective Action, PIC, Deadline, Status
- Status badges dengan workflow:
  - draft (gray), open (blue), in_progress (yellow)
  - awaiting_verification (purple)
  - verified (green), rejected (red), archived (gray)

**Detail Modal**:
- Root Cause Analysis form (dropdown kategory + textarea)
- Corrective Action form + upload evidence
- Preventive Action form + upload evidence
- Timeline (history perubahan)
- Tombol: Submit Verification, Verify (LPM only), Reject

### 4.5 Komponen Shared Baru

#### `Components/SPMI/SeverityBadge.tsx`
```tsx
interface Props {
    severity: 'ringan' | 'sedang' | 'berat' | 'kritis';
    size?: 'sm' | 'md';
}
// ringan → bg-green-100 text-green-800
// sedang → bg-yellow-100 text-yellow-800
// berat → bg-orange-100 text-orange-800
// kritis → bg-red-100 text-red-800
```

#### `Components/SPMI/StatusBadge.tsx`
```tsx
interface Props {
    status: string; // draft → gray, submitted → blue, assigned → indigo, dll
    workflowType: 'audit' | 'capa' | 'dokumen';
}
// Mapping warna per workflow type
```

#### `Components/SPMI/WorkflowDropdown.tsx`
```tsx
interface Props {
    currentStatus: string;
    workflowType: 'audit' | 'capa';
    onTransition: (toStatus: string) => void;
    transitions: string[]; // state machine
    disabled?: boolean;
}
// Dropdown button → list transisi yang valid
// Confirmation dialog via Modal
```

#### `Components/SPMI/Timeline.tsx`
```tsx
interface TimelineItem {
    date: string;
    action: string;
    user: string;
    description?: string;
    type: 'created' | 'updated' | 'transition' | 'verified' | 'rejected';
}
// Visual timeline dengan icon per type
// Created → green dot
// Updated → blue dot
// Transition → yellow dot
// Verified → check circle
// Rejected → x circle
```

### 4.6 State Management Patterns

```tsx
// Di setiap halaman SPMI, gunakan pattern:

interface Props {
    audit: PaginatedData<AuditItem>;
    dashboard_stats: DashboardStats;
    standar_list: StandarItem[];
    prodi_list: ProdiItem[];
    periode_list: PeriodeItem[];
    success?: string;
    warning?: string;
}

// Form state:
const form = useForm({
    prodi_id: '',
    periode_id: '',
    judul_audit: '',
    tanggal_audit: '',
    temuan: '',
    rekomendasi: '',
    standar_mutu_id: '',
    severity: 'ringan',
    // ...
});

// Filter state:
const [filters, setFilters] = useState({
    search: '',
    status: '',
    standar_mutu_id: '',
    severity: '',
    prodi_id: '',
    periode_id: '',
});

// Debounce search:
useEffect(() => {
    const timer = setTimeout(() => {
        router.get(route('spmi.audit'), filters, { preserveState: true, replace: true });
    }, 500);
    return () => clearTimeout(timer);
}, [filters.search, filters.status, filters.standar_mutu_id, filters.severity]);
```

---

## 5. AI INTEGRATION

### 5.1 Klasifikasi Temuan Otomatis (RAG-based)

**Flow**:
```
User input temuan (text)
  → POST /spmi/audit/{id}/ai-resolve
  → AuditMutuController@aiResolve
  → RAGService@ask("Klasifikasikan temuan ini ke standar mutu: {temuan}")
  → Python AI Service: embedding → vector search → Gemini answer
  → Return: { detected_standar, suggestion, sources }
```

**File perubahan**:
- `AuditMutuController@aiResolve` — ganti dari `str_contains` ke `RAGService@ask`

### 5.2 Auto-PIC Recommendation

**Flow**:
```
Temuan baru dengan severity >= sedang
  → Event AuditSevereFindingCreated
  → Listener: AutoAssignPic
  → Query: cari user dengan role terkait (LPM/auditor) yang prodi relevan
  → Cek beban kerja (jumlah CAPA aktif < threshold)
  → Assign ke user dengan beban paling ringan
  → Auto-create Peringatan ke PIC
```

### 5.3 AI-Powered Recommendations via Python Agents

**Integrasi dengan MCP existing**:
```
Laravel → MCPClientService@callTool('rekomendasi_generate', { prodi_id, konteks: 'audit' })
  → Python: rekomendasi_agent.execute({ prodi_id, context: 'audit_temuan' })
  → Query DB: ambil temuan open + severity
  → Generate rekomendasi based on gap analysis
  → Return: [{rekomendasi, prioritas, referensi_standar}]
```

### 5.4 Early Warning via Peringatan System (existing)

**Integrasi dengan sistem peringatan existing**:
```
Schedule: daily
  → Check CAPA overdue (deadline < 0)
  → Check deadline mendekat (deadline in 7 days)
  → Check temuan kritis tanpa CAPA > 3 hari
  → Auto-create AgentPeringatanLog (critical/warning/info)
```

---

## 6. DASHBOARD SPMI

### 6.1 Layout Dashboard

```
┌─────────────────────────────────────────────────────────────────┐
│  🏠 Dashboard > SPMI > Dashboard Penjaminan Mutu                │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Periode: [2025/2026 ▼]  Prodi: [Semua ▼]  Refresh: 🔄  │   │
│  └──────────────────────────────────────────────────────────┘   │
├─────────────────────────────────────────────────────────────────┤
│  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐        │
│  │Total │ │Open  │ │Close │ │ Rate │ │ Skor │ │ CAPA │        │
│  │Temuan│ │      │ │      │ │      │ │Mutu  │ │Overdue│        │
│  │  42  │ │  12  │ │  30  │ │ 76%  │ │ 82.5 │ │  3   │        │
│  └──────┘ └──────┘ └──────┘ └──────┘ └──────┘ └──────┘        │
├─────────────────────────────────────────────────────────────────┤
│  PPEPP CYCLE PROGRESS                                  [Kelola]│
│  P ████████████████████ 100%  P ██████████████░░░  80%         │
│  E ████████░░░░░░░░░░░░  45%  P ████░░░░░░░░░░░░░  20%         │
│  P ░░░░░░░░░░░░░░░░░░░░   0%                                   │
├──────────────────┬──────────────────────────────────────────────┤
│  📊 TREN TEMUAN  │  🥧 SEVERITY DISTRIBUTION                    │
│                  │                                              │
│  [BarChart]      │  [PieChart]                                  │
│                  │                                              │
│                  │   Ringan: 15  Sedang: 12                     │
│                  │   Berat: 3    Kritis: 1                      │
├──────────────────┴──────────────────────────────────────────────┤
│  ⚠ EARLY WARNING                                                │
│  🟥 Kritis: Prodi Informatika - 1 temuan kritis, belum ditindak │
│  🟡 Overdue: 3 CAPA lewat deadline                              │
│  🟡 Deadline < 7 hari: 5 temuan                                 │
│  ℹ️ Tanpa PIC: 2 temuan belum ditugaskan                        │
├─────────────────────────────────────────────────────────────────┤
│  TEMUAN PER STANDAR MUTU                                        │
│  Pendidikan     ████████████████████ 4 temuan (2 berat)         │
│  Penelitian     ████████████████████ 3 temuan (1 berat)         │
│  PKM            ████████             1 temuan (ringan)          │
│  Tambahan       ██████████████████████████████ 10 temuan        │
├─────────────────────────────────────────────────────────────────┤
│  RANKING PRODI BERDASARKAN SKOR MUTU                            │
│  #1 Informatika        ⭐⭐⭐⭐⭐  92.5                            │
│  #2 Sistem Informasi   ⭐⭐⭐⭐   85.0                            │
│  #3 Manajemen          ⭐⭐⭐⭐   78.3                            │
│  #4 Akuntansi          ⭐⭐⭐     65.0 ← 1 temuan kritis         │
└─────────────────────────────────────────────────────────────────┘
```

### 6.2 Data Flow Dashboard

```
[Page Load]
  → SpmiDashboardController@overview
  → SpmiDashboardService@getOverview(prodiId, periodeId)
  → Query:
    - total_temuan = COUNT(*) WHERE periode_id=?
    - open_temuan = COUNT(*) WHERE status IN ('draft','submitted','assigned','in_progress')
    - closed_temuan = COUNT(*) WHERE status IN ('closed','archived')
    - close_rate = closed_temuan / total_temuan * 100
    - skor_mutu = calculated dari severity weighting
    - capa_overdue = COUNT(*) WHERE deadline < NOW() AND status NOT IN ('closed','archived')
  → PPEPP progress dari SpmiCycle
  → Trends dari AuditHistory (aggregated per bulan)
  → Early warning dari query conditions

[Chart Data via AJAX]
  → GET /spmi/dashboard/chart-data?prodi_id=X&periode_id=Y
  → return JSON untuk Recharts

[Polling every 60s]
  → GET /spmi/dashboard/overview?prodi_id=X&periode_id=Y
  → update early warning + KPI cards tanpa reload page
```

---

## 7. ANTI-BUG PATTERNS

### 7.1 Database Level

```php
// 1. UNIQUE CONSTRAINTS — cegah duplikasi data
$table->unique(['prodi_id', 'periode_id', 'standar_mutu_id']); // EDPS
$table->unique(['nomor_dokumen']); // Dokumen Mutu

// 2. FOREIGN KEYS — jaga referential integrity
$table->foreignId('prodi_id')->constrained('m_prodi')->restrictOnDelete();
// Gunakan restrictOnDelete() bukan cascadeOnDelete() untuk data penting

// 3. CHECK CONSTRAINTS — validasi di level DB
// PostgreSQL:
DB::statement("ALTER TABLE trx_audit_mutu ADD CONSTRAINT severity_check CHECK (severity IN ('ringan','sedang','berat','kritis'))");

// 4. INDEXES — untuk query performance
$table->index(['prodi_id', 'periode_id', 'status']);
$table->index(['audit_mutu_id', 'created_at']); // history
$table->index(['pic_user_id', 'status']); // CAPA
$table->index(['deadline', 'status']); // untuk scheduler overdue check
```

### 7.2 Application Level

```php
// 1. DB TRANSACTION + LOCKING
DB::transaction(function () {
    // Lock row untuk cegah race condition
    $audit = AuditMutu::where('id', $id)->lockForUpdate()->first();

    // Validasi state machine
    throw_if(
        !$this->canTransition($audit->status, $request->status),
        ValidationException::withMessages(['status' => 'Transisi tidak valid'])
    );

    // Operasi kritis
    $audit->update(['status' => $request->status]);
    AuditHistory::log(...);
});

// 2. IDEMPOTENCY — safe untuk di-call multiple times
public function closeAudit(AuditMutu $audit): void {
    if ($audit->status === 'closed') {
        return; // sudah closed, skip
    }
    // ... logic
}

// 3. OPTIMISTIC LOCKING — cegah overwrite data
$audit->update([
    'tindak_lanjut' => $request->tindak_lanjut,
    'updated_at' => now(),
]); // Jika ada yang update duluan, updated_at beda → gunakan version column

// 4. INPUT SANITIZATION
$temuan = strip_tags($request->temuan); // cegah XSS
$temuan = preg_replace('/[^\p{L}\p{N}\s\.\,\!\?\-\/\n\r]/u', '', $temuan); // hanya karakter aman

// 5. RATE LIMITING
Route::post('/spmi/audit/{auditMutu}/ai-resolve', ...)->middleware('throttle:30,1');

// 6. AUTHORIZATION di SETIAP method
public function update(AuditMutuRequest $request, AuditMutu $auditMutu) {
    $this->authorize('update', $auditMutu); // Policy
    // ... atau manual:
    if ($auditMutu->is_locked) {
        abort(403, 'Temuan sudah diverifikasi dan tidak bisa diedit.');
    }
}
```

### 7.3 Frontend Level

```tsx
// 1. OPTIMISTIC UI FAILOVER
const submit = async (e: FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    try {
        if (editing) {
            await router.put(route('spmi.audit.update', editing.id), data);
        } else {
            await router.post(route('spmi.audit.store'), data);
        }
    } catch (error) {
        // Rollback UI state
        setSubmitting(false);
        showError('Gagal menyimpan data. Silakan coba lagi.');
    }
};

// 2. DEBOUNCED SEARCH (existing pattern)
useEffect(() => {
    const timer = setTimeout(() => {
        router.get(route('spmi.audit'), filters, {
            preserveState: true,
            replace: true,
            only: ['audit', 'dashboard_stats'], // hanya refresh data ini
        });
    }, 500);
    return () => clearTimeout(timer);
}, [filters]);

// 3. CONFIRMATION SEBELUM DESTRUCTIVE ACTION
const confirmTransition = (toStatus: string) => {
    if (['closed', 'archived'].includes(toStatus)) {
        setShowConfirm(true);
        setPendingTransition(toStatus);
        return;
    }
    executeTransition(toStatus);
};

// 4. LOADING STATE
const [pageLoading, setPageLoading] = useState(false);
// Tampilkan Skeleton saat loading
if (pageLoading) return <SkeletonTable rows={5} cols={8} />;

// 5. ERROR BOUNDARY
<ErrorBoundary fallback={<div>Terjadi kesalahan. Muat ulang halaman.</div>}>
    <SpmiDashboard />
</ErrorBoundary>

// 6. VALIDASI FORM SIDE
const validateForm = (data: FormData): Record<string, string> => {
    const errors: Record<string, string> = {};
    if (!data.judul_audit) errors.judul_audit = 'Judul audit wajib diisi';
    if (!data.tanggal_audit) errors.tanggal_audit = 'Tanggal audit wajib diisi';
    if (data.temuan && data.temuan.length > 5000) errors.temuan = 'Temuan maksimal 5000 karakter';
    return errors;
};
```

### 7.4 Testing Strategy

```php
// 1. UNIT TEST — Service layer
class SpmiWorkflowServiceTest extends TestCase {
    /** @test */
    public function it_cannot_transition_from_closed_to_in_progress() {
        $service = new SpmiWorkflowService();
        $this->assertFalse($service->canTransition('closed', 'in_progress'));
    }

    /** @test */
    public function it_creates_capa_on_severe_finding() {
        // Create audit with severity 'kritis'
        // Assert CAPA created
    }
}

// 2. FEATURE TEST — Controller
class AuditMutuControllerTest extends TestCase {
    /** @test */
    public function it_rejects_update_when_locked() {
        $audit = AuditMutu::factory()->create(['is_locked' => true]);
        $response = $this->putJson("/spmi/audit/{$audit->id}", [
            'judul_audit' => 'try to edit',
        ]);
        $response->assertStatus(403);
    }
}

// 3. FRONTEND TEST — Component
// test('shows correct severity colors', () => {
//     render(<SeverityBadge severity="kritis" />);
//     expect(screen.getByText('KRITIS')).toHaveClass('bg-red-100');
// });
```

---

## 8. DATABASE SYNC & INTEGRASI

### 8.1 Cross-Module Integration Matrix

| Event | Trigger | Action |
|-------|---------|--------|
| Audit temuan kritis | `AuditSevereFindingCreated` | Auto-create Risk Register (kategori: mutu) |
| CAPA butuh pengadaan | `CapaCreated` + sarana | Create usulan RKAT (otomatis di-flag) |
| Temuan IKU-related | `AuditStatusChanged` | Update cascading IKU capaian |
| Deadline mendekat | Scheduler harian | Create Peringatan (warning) ke PIC |
| CAPA overdue > 7 hari | Scheduler harian | Create Peringatan (critical) ke PIC + eskalasi ke atasan |
| Audit closed | `AuditStatusChanged` → closed | Recalculate skor mutu prodi |
| Siklus PPEPP selesai | `SpmiCycleCompleted` | Naikkan target standar untuk siklus berikutnya |

### 8.2 Sinkronisasi Data Standar Mutu

```php
// Saat standar mutu di-update:
// → Jika target_nilai berubah → buat EDPS baru untuk setiap prodi aktif
// → Jika standar dinonaktifkan → archive temuan terkait

class StandarMutuService {
    public function update(StandarMutu $standar, array $data): void {
        DB::transaction(function () use ($standar, $data) {
            // 1. Update standar
            $standar->update($data);

            // 2. Jika target berubah → sinkron ke EDPS prodi aktif
            if (isset($data['target_nilai'])) {
                $prodiAktif = Prodi::where('is_active', true)->get();
                foreach ($prodiAktif as $prodi) {
                    Edps::updateOrCreate(
                        ['prodi_id' => $prodi->id, 'standar_mutu_id' => $standar->id],
                        ['target' => $data['target_nilai']]
                    );
                }
            }
        });
    }
}
```

### 8.3 Sinkronisasi dengan IKU (existing)

```php
// Event: AuditStatusChanged → closed
class SyncAuditToIku {
    public function handle(AuditStatusChanged $event): void {
        // Jika temuan terkait IKU tertentu (from temuan text)
        $ikuCode = $this->detectIkuFromText($event->audit->temuan);
        if (!$ikuCode) return;

        // Update capaian cascading IKU
        $cascading = CascadingIku::whereHas('iku', fn($q) =>
            $q->where('kode_iku', $ikuCode)
        )->where('prodi_id', $event->audit->prodi_id)
         ->where('status', 'active')
         ->first();

        if ($cascading) {
            $cascading->increment('capaian'); // atau logika lain
        }
    }
}
```

### 8.4 Sinkronisasi dengan Risk Register (existing)

```php
// Event: AuditSevereFindingCreated
class CreateRiskFromAudit {
    public function handle(AuditSevereFindingCreated $event): void {
        RiskRegister::create([
            'prodi_id' => $event->audit->prodi_id,
            'periode_id' => $event->audit->periode_id,
            'nama_risiko' => "Temuan Audit: {$event->audit->judul_audit}",
            'kategori' => 'mutu',
            'dampak' => 'tinggi',
            'probabilitas' => 'sedang', // atau dari data historis
            'skor_risiko' => $this->calculateRiskScore($event->audit),
            'mitigasi' => $event->audit->rekomendasi,
            'status' => 'open',
            'penanggung_jawab' => optional($event->audit->picUser)->name,
        ]);
    }

    private function calculateRiskScore(AuditMutu $audit): string {
        return match ($audit->severity) {
            'kritis' => '20',
            'berat' => '15',
            'sedang' => '10',
            'ringan' => '5',
        };
    }
}
```

### 8.5 Python Agent Sync (MCP)

```php
class SyncAuditToPythonAgent {
    public function handle(AuditStatusChanged $event): void {
        // Trigger Python agent untuk analisis lanjutan
        // Hanya untuk severity >= sedang
        if (!in_array($event->audit->severity, ['sedang', 'berat', 'kritis'])) return;

        dispatch(function () use ($event) {
            $mcp = app(MCPClientService::class);

            // 1. Generate rekomendasi dari AI
            $rekomendasi = $mcp->callTool('rekomendasi_generate', [
                'prodi_id' => $event->audit->prodi_id,
                'konteks' => "Temuan audit: {$event->audit->temuan}",
            ]);

            // 2. Update rekomendasi di audit
            if ($rekomendasi['success']) {
                $event->audit->update([
                    'rekomendasi_ai' => json_encode($rekomendasi['data']),
                ]);
            }

            // 3. Run peringatan check untuk PIC
            $mcp->callTool('peringatan_check', [
                'prodi_id' => $event->audit->prodi_id,
            ]);
        })->onQueue('spmi');
    }
}
```

---

## 9. SPRINT BREAKDOWN

### Sprint 1: Database & Models (Hari 1-3)

| Task | File | Detail |
|------|------|--------|
| Migration Standar Mutu | `database/migrations/*_create_m_standar_mutu_table.php` | 36 standar, unique kode_standar |
| Migration CAPA | `database/migrations/*_create_trx_capa_table.php` | Semua kolom RCA + corrective + preventive |
| Migration Audit Enhancement | `database/migrations/*_add_spmi_columns_to_trx_audit_mutu_table.php` | standar_mutu_id, severity, PIC, workflow, is_locked |
| Migration Audit History | `database/migrations/*_create_trx_audit_mutu_histories_table.php` | Audit trail |
| Migration EDPS | `database/migrations/*_create_trx_edps_table.php` | Evaluasi Diri |
| Migration RTM | `database/migrations/*_create_trx_rtm_table.php` | Rapat Tinjauan Manajemen |
| Migration Dokumen Mutu | `database/migrations/*_create_m_spmi_dokumen_table.php` | Dokumen mutu + version |
| Migration Survey | `database/migrations/*_create_trx_survey_spmi_table.php` | Survey stakeholder |
| Seeder Standar Mutu | `database/seeders/StandarMutuSeeder.php` | 36 standar + update SpmiCycle |
| Models | `app/Models/StandarMutu.php` | + relasi |
| Models | `app/Models/Capa.php` | + relasi |
| Models | `app/Models/AuditHistory.php` | + relasi |
| Models | `app/Models/Edps.php` | + relasi |
| Models | `app/Models/Rtm.php` | + relasi |
| Models | `app/Models/RtmActionItem.php` | + relasi |
| Models | `app/Models/SpmiDokumen.php` | + relasi |
| Models | `app/Models/SurveySpmi.php` | + relasi |

### Sprint 2: Backend Services (Hari 4-6)

| Task | File | Detail |
|------|------|--------|
| Service | `app/Services/SPMI/SpmiWorkflowService.php` | State machine logic |
| Service | `app/Services/SPMI/AuditAnalysisService.php` | Agregasi + scoring + trend |
| Service | `app/Services/SPMI/CapaService.php` | CAPA CRUD + workflow |
| Service | `app/Services/SPMI/SpmiDashboardService.php` | Dashboard data |
| Service | `app/Services/SPMI/SpmiReportService.php` | Laporan LLDIKTI |
| Service | `app/Services/SPMI/StandarMutuService.php` | Standar sync to EDPS |
| Events | `app/Events/AuditStatusChanged.php` | |
| Events | `app/Events/AuditSevereFindingCreated.php` | |
| Events | `app/Events/CapaDeadlineApproaching.php` | |
| Listeners | `app/Listeners/CreateRiskRegisterFromSevereFinding.php` | |
| Listeners | `app/Listeners/SendPeringatanOnAuditAssignment.php` | |
| Listeners | `app/Listeners/SyncAuditToIku.php` | |
| Listeners | `app/Listeners/SyncAuditToPythonAgent.php` | |
| Jobs | `app/Jobs/CheckCapaDeadline.php` | Scheduler harian |

### Sprint 3: Controllers (Hari 7-9)

| Task | File | Detail |
|------|------|--------|
| Enhanced Controller | `app/Http/Controllers/Api/AuditMutuController.php` | + workflow + AI resolve real |
| New Controller | `app/Http/Controllers/Api/CapaController.php` | CRUD + verify + reject |
| New Controller | `app/Http/Controllers/Api/SpmiCycleController.php` | PPEPP cycle management |
| New Controller | `app/Http/Controllers/Api/SpmiDashboardController.php` | Overview + chart data |
| New Controller | `app/Http/Controllers/Api/StandarMutuController.php` | CRUD standar |
| New Controller | `app/Http/Controllers/Api/SpmiDokumenController.php` | Dokumen management |
| New Controller | `app/Http/Controllers/Api/EdpsController.php` | Evaluasi Diri |
| New Controller | `app/Http/Controllers/Api/RtmController.php` | RTM + action items |
| New Controller | `app/Http/Controllers/Api/SurveySpmiController.php` | Survey management |
| Form Request | `app/Http/Requests/AuditMutuRequest.php` | Enhanced validation |
| Form Request | `app/Http/Requests/CapaRequest.php` | |
| Form Request | `app/Http/Requests/StandarMutuRequest.php` | |
| Routes | `routes/web.php` | Tambah routes baru |

### Sprint 4: Frontend Dashboard (Hari 10-12)

| Task | File | Detail |
|------|------|--------|
| Halaman | `resources/js/Pages/Spmi/Dashboard.tsx` | Halaman utama |
| Komponen | `resources/js/Components/SPMI/KpiCard.tsx` | KPI card dengan icon + trend |
| Komponen | `resources/js/Components/SPMI/PpeppProgress.tsx` | PPEPP cycle progress bar |
| Komponen | `resources/js/Components/SPMI/EarlyWarning.tsx` | Early warning panel |
| Komponen | `resources/js/Components/SPMI/StandarChart.tsx` | Temuan per standar chart |
| Komponen | `resources/js/Components/SPMI/ProdiRanking.tsx` | Ranking prodi |
| Sub-halaman | `resources/js/Pages/Spmi/Audit/Partials/AuditTable.tsx` | Table with all new columns |

### Sprint 5: Frontend CRUD + Detail (Hari 13-15)

| Task | File | Detail |
|------|------|--------|
| Enhanced Page | `resources/js/Pages/Spmi/Audit/Index.tsx` | + workflow + severity + CAPA link |
| Halaman | `resources/js/Pages/Spmi/Audit/Detail.tsx` | Detail + timeline |
| Halaman | `resources/js/Pages/Spmi/Capa/Index.tsx` | List CAPA |
| Halaman | `resources/js/Pages/Spmi/Capa/Detail.tsx` | Detail + RCA form + evidence |
| Halaman | `resources/js/Pages/Spmi/StandarMutu/Index.tsx` | CRUD standar |
| Halaman | `resources/js/Pages/Spmi/Cycle/Index.tsx` | PPEPP management |
| Komponen | `resources/js/Components/SPMI/SeverityBadge.tsx` | |
| Komponen | `resources/js/Components/SPMI/StatusBadge.tsx` | |
| Komponen | `resources/js/Components/SPMI/WorkflowDropdown.tsx` | |
| Komponen | `resources/js/Components/SPMI/Timeline.tsx` | Visual timeline |

### Sprint 6: AI Integration + Testing (Hari 16-18)

| Task | File | Detail |
|------|------|--------|
| AI Fix | `AuditMutuController@aiResolve` | Ganti str_contains → RAGService |
| Tests | `tests/Feature/Http/AuditMutuControllerTest.php` | Enhanced |
| Tests | `tests/Feature/Http/CapaControllerTest.php` | |
| Tests | `tests/Feature/Http/SpmiDashboardControllerTest.php` | |
| Tests | `tests/Unit/Services/SpmiWorkflowServiceTest.php` | |
| Tests | `tests/Unit/Services/AuditAnalysisServiceTest.php` | |
| Integration | Events + Listeners wiring | `app/Providers/EventServiceProvider.php` |
| Scheduler | `app/Console/Kernel.php` | CheckCapaDeadline daily |
| Permission | `database/seeders/RolePermissionSeeder.php` | Tambah permission SPMI baru |

---

## RINGKASAN PERUBAHAN FILE

| Kategori | Jumlah File | Baris Estimasi |
|----------|-------------|----------------|
| Migrations | 7 | ~400 |
| Models | 8 | ~200 |
| Services | 6 | ~800 |
| Events/Listeners | 6 | ~200 |
| Jobs | 1 | ~50 |
| Controllers | 8 | ~600 |
| Form Requests | 3 | ~100 |
| Routes | 1 | ~50 |
| Frontend Pages | 8 | ~2000 |
| Frontend Components | 6 | ~400 |
| Seeders | 2 | ~200 |
| Tests | 4 | ~400 |
| EventServiceProvider | 1 | ~30 |
| Console/Kernel | 1 | ~20 |
| **TOTAL** | **~62 file** | **~5500 baris** |

---

## POLA KODE ANTI-BUG — CHEAT SHEET

```php
// ┌─────────────────────────────────────────────────────────────┐
// │  WAJIB di setiap operasi write:                             │
// │  1. DB::transaction + lockForUpdate()                       │
// │  2. Validasi state machine                                   │
// │  3. Cek is_locked / immutable flag                          │
// │  4. AuditHistory::log()                                     │
// │  5. strip_tags() pada input text                            │
// │  6. Authorize permission                                    │
// │  7. Rate limiting pada AI endpoint                          │
// └─────────────────────────────────────────────────────────────┘
```

```tsx
// ┌─────────────────────────────────────────────────────────────┐
// │  WAJIB di setiap halaman:                                   │
// │  1. ErrorBoundary wrapper                                   │
// │  2. Loading state (Skeleton)                                │
// │  3. Debounce search (500ms)                                 │
// │  4. Confirmation sebelum destructive action                 │
// │  5. Form validation client + server                         │
// │  6. Rollback on error                                       │
// │  7. Permission-based UI rendering                           │
// └─────────────────────────────────────────────────────────────┘
```
