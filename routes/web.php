<?php

use App\Http\Controllers\Api\AdminSettingController;
use App\Http\Controllers\Api\AiptController;
use App\Http\Controllers\Api\AlumniController;
use App\Http\Controllers\Api\AuditMutuController;
use App\Http\Controllers\Api\BkdController;
use App\Http\Controllers\Api\CapaController;
use App\Http\Controllers\Api\EdpsController;
use App\Http\Controllers\Api\RtmController;
use App\Http\Controllers\Api\SpmiCycleController;
use App\Http\Controllers\Api\SpmiDashboardController;
use App\Http\Controllers\Api\SpmiDokumenController;
use App\Http\Controllers\Api\StandarMutuController;
use App\Http\Controllers\Api\SurveySpmiController;
use App\Http\Controllers\Api\CplController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DokumenBuktiController;
use App\Http\Controllers\Api\DosenController;
use App\Http\Controllers\Api\FakultasController;
use App\Http\Controllers\Api\GeneratorController;
use App\Http\Controllers\Api\IndikatorAkreditasiController;
use App\Http\Controllers\Api\InstrumenAkreditasiController;
use App\Http\Controllers\Api\IntegrasiController;
use App\Http\Controllers\Api\KegiatanPendidikanController;
use App\Http\Controllers\Api\KerjasamaController;
use App\Http\Controllers\Api\KeuanganController;
use App\Http\Controllers\Api\KnowledgeBaseController;
use App\Http\Controllers\Api\KuisionerTracerController;
use App\Http\Controllers\Api\KurikulumController;
use App\Http\Controllers\Api\KurikulumMappingController;
use App\Http\Controllers\Api\LembagaAkreditasiController;
use App\Http\Controllers\Api\MahasiswaBimbinganController;
use App\Http\Controllers\Api\MataKuliahController;
use App\Http\Controllers\Api\MitraController;
use App\Http\Controllers\Api\PenelitianController;
use App\Http\Controllers\Api\PenunjangController;
use App\Http\Controllers\Api\PeringatanController;
use App\Http\Controllers\Api\PeriodeAkademikController;
use App\Http\Controllers\Api\PkmController;
use App\Http\Controllers\Api\PortofolioController;
use App\Http\Controllers\Api\PrediksiController;
use App\Http\Controllers\Api\ProdiController;
use App\Http\Controllers\Api\PublikasiController;
use App\Http\Controllers\Api\RekomendasiController;
use App\Http\Controllers\Api\RiskRegisterController;
use App\Http\Controllers\Api\RoleSwitchController;
use App\Http\Controllers\Api\RpsController;
use App\Http\Controllers\Api\SaranaController;
use App\Http\Controllers\Api\SintaImportController;
use App\Http\Controllers\Api\DataImportController;
use App\Http\Controllers\Api\ReconciliationController;
use App\Http\Controllers\Api\IkuController;
use App\Http\Controllers\Api\CascadingIkuController;
use App\Http\Controllers\Api\RkatController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\TracerJawabanController;
use App\Http\Controllers\Api\VerifikasiController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\PermissionMiddleware;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', PermissionMiddleware::class, 'throttle:crud'])->group(function () {

    Route::post('/role/switch', [RoleSwitchController::class, 'switch'])->name('role.switch');

    // RAG Chatbot
    Route::post('/rag/ask', [KnowledgeBaseController::class, 'ask'])->name('rag.ask');
    Route::get('/rag/status', [KnowledgeBaseController::class, 'status'])->name('rag.status');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Master Data Routes
    Route::get('/master-data/fakultas', [FakultasController::class, 'index'])->name('master-data.fakultas');
    Route::post('/master-data/fakultas', [FakultasController::class, 'store'])->name('master-data.fakultas.store');
    Route::put('/master-data/fakultas/{fakultas}', [FakultasController::class, 'update'])->name('master-data.fakultas.update');
    Route::delete('/master-data/fakultas/{fakultas}', [FakultasController::class, 'destroy'])->name('master-data.fakultas.destroy');

    Route::get('/master-data/prodi', [ProdiController::class, 'index'])->name('master-data.prodi');
    Route::post('/master-data/prodi', [ProdiController::class, 'store'])->name('master-data.prodi.store');
    Route::put('/master-data/prodi/{prodi}', [ProdiController::class, 'update'])->name('master-data.prodi.update');
    Route::delete('/master-data/prodi/{prodi}', [ProdiController::class, 'destroy'])->name('master-data.prodi.destroy');

    Route::get('/master-data/dosen', [DosenController::class, 'index'])->name('master-data.dosen');
    Route::post('/master-data/dosen', [DosenController::class, 'store'])->name('master-data.dosen.store');
    Route::put('/master-data/dosen/{dosen}', [DosenController::class, 'update'])->name('master-data.dosen.update');
    Route::delete('/master-data/dosen/{dosen}', [DosenController::class, 'destroy'])->name('master-data.dosen.destroy');

    Route::get('/master-data/mata-kuliah', [MataKuliahController::class, 'index'])->name('master-data.mata-kuliah');
    Route::post('/master-data/mata-kuliah', [MataKuliahController::class, 'store'])->name('master-data.mata-kuliah.store');
    Route::put('/master-data/mata-kuliah/{mata_kuliah}', [MataKuliahController::class, 'update'])->name('master-data.mata-kuliah.update');
    Route::delete('/master-data/mata-kuliah/{mata_kuliah}', [MataKuliahController::class, 'destroy'])->name('master-data.mata-kuliah.destroy');

    Route::get('/master-data/kurikulum', [KurikulumController::class, 'index'])->name('master-data.kurikulum');
    Route::post('/master-data/kurikulum', [KurikulumController::class, 'store'])->name('master-data.kurikulum.store');
    Route::put('/master-data/kurikulum/{kurikulum}', [KurikulumController::class, 'update'])->name('master-data.kurikulum.update');
    Route::delete('/master-data/kurikulum/{kurikulum}', [KurikulumController::class, 'destroy'])->name('master-data.kurikulum.destroy');

    Route::get('/master-data/cpl', [CplController::class, 'index'])->name('master-data.cpl');
    Route::post('/master-data/cpl', [CplController::class, 'store'])->name('master-data.cpl.store');
    Route::put('/master-data/cpl/{cpl}', [CplController::class, 'update'])->name('master-data.cpl.update');
    Route::delete('/master-data/cpl/{cpl}', [CplController::class, 'destroy'])->name('master-data.cpl.destroy');

    Route::get('/master-data/periode-akademik', [PeriodeAkademikController::class, 'index'])->name('master-data.periode-akademik');
    Route::post('/master-data/periode-akademik', [PeriodeAkademikController::class, 'store'])->name('master-data.periode-akademik.store');
    Route::put('/master-data/periode-akademik/{periode_akademik}', [PeriodeAkademikController::class, 'update'])->name('master-data.periode-akademik.update');
    Route::delete('/master-data/periode-akademik/{periode_akademik}', [PeriodeAkademikController::class, 'destroy'])->name('master-data.periode-akademik.destroy');

    // Portofolio Routes
    Route::get('/portofolio', [PortofolioController::class, 'index'])->name('portofolio');
    Route::get('/portofolio/pendidikan', [KegiatanPendidikanController::class, 'index'])->name('portofolio.pendidikan');
    Route::post('/portofolio/pendidikan', [KegiatanPendidikanController::class, 'store'])->name('portofolio.pendidikan.store');
    Route::put('/portofolio/pendidikan/{kegiatanPendidikan}', [KegiatanPendidikanController::class, 'update'])->name('portofolio.pendidikan.update');
    Route::delete('/portofolio/pendidikan/{kegiatanPendidikan}', [KegiatanPendidikanController::class, 'destroy'])->name('portofolio.pendidikan.destroy');

    Route::get('/portofolio/penelitian', [PenelitianController::class, 'index'])->name('portofolio.penelitian');
    Route::post('/portofolio/penelitian', [PenelitianController::class, 'store'])->name('portofolio.penelitian.store');
    Route::put('/portofolio/penelitian/{penelitian}', [PenelitianController::class, 'update'])->name('portofolio.penelitian.update');
    Route::delete('/portofolio/penelitian/{penelitian}', [PenelitianController::class, 'destroy'])->name('portofolio.penelitian.destroy');

    Route::get('/portofolio/publikasi', [PublikasiController::class, 'index'])->name('portofolio.publikasi');
    Route::post('/portofolio/publikasi', [PublikasiController::class, 'store'])->name('portofolio.publikasi.store');
    Route::put('/portofolio/publikasi/{publikasi}', [PublikasiController::class, 'update'])->name('portofolio.publikasi.update');
    Route::delete('/portofolio/publikasi/{publikasi}', [PublikasiController::class, 'destroy'])->name('portofolio.publikasi.destroy');

    Route::get('/portofolio/pkm', [PkmController::class, 'index'])->name('portofolio.pkm');
    Route::post('/portofolio/pkm', [PkmController::class, 'store'])->name('portofolio.pkm.store');
    Route::put('/portofolio/pkm/{pkm}', [PkmController::class, 'update'])->name('portofolio.pkm.update');
    Route::delete('/portofolio/pkm/{pkm}', [PkmController::class, 'destroy'])->name('portofolio.pkm.destroy');

    Route::get('/portofolio/penunjang', [PenunjangController::class, 'index'])->name('portofolio.penunjang');
    Route::post('/portofolio/penunjang', [PenunjangController::class, 'store'])->name('portofolio.penunjang.store');
    Route::put('/portofolio/penunjang/{penunjang}', [PenunjangController::class, 'update'])->name('portofolio.penunjang.update');
    Route::delete('/portofolio/penunjang/{penunjang}', [PenunjangController::class, 'destroy'])->name('portofolio.penunjang.destroy');

    // BKD Routes
    Route::get('/bkd', [BkdController::class, 'index'])->name('bkd');
    Route::post('/bkd', [BkdController::class, 'store'])->name('bkd.store');
    Route::put('/bkd/{bkd}', [BkdController::class, 'update'])->name('bkd.update');
    Route::delete('/bkd/{bkd}', [BkdController::class, 'destroy'])->name('bkd.destroy');

    // Dokumen Routes
    Route::get('/dokumen', [DokumenBuktiController::class, 'index'])->name('dokumen');
    Route::post('/dokumen', [DokumenBuktiController::class, 'store'])->name('dokumen.store');
    Route::put('/dokumen/{dokumenBukti}', [DokumenBuktiController::class, 'update'])->name('dokumen.update');
    Route::delete('/dokumen/{dokumenBukti}', [DokumenBuktiController::class, 'destroy'])->name('dokumen.destroy');

    // Bimbingan Routes
    Route::get('/bimbingan', [MahasiswaBimbinganController::class, 'index'])->name('bimbingan');
    Route::post('/bimbingan', [MahasiswaBimbinganController::class, 'store'])->name('bimbingan.store');
    Route::put('/bimbingan/{mahasiswaBimbingan}', [MahasiswaBimbinganController::class, 'update'])->name('bimbingan.update');
    Route::delete('/bimbingan/{mahasiswaBimbingan}', [MahasiswaBimbinganController::class, 'destroy'])->name('bimbingan.destroy');

    // SPMI Routes
    Route::get('/spmi/audit', [AuditMutuController::class, 'index'])->name('spmi.audit');
    Route::get('/spmi/audit/{auditMutu}', [AuditMutuController::class, 'show'])->name('spmi.audit.show');
    Route::post('/spmi/audit', [AuditMutuController::class, 'store'])->name('spmi.audit.store');
    Route::put('/spmi/audit/{auditMutu}', [AuditMutuController::class, 'update'])->name('spmi.audit.update');
    Route::post('/spmi/audit/{auditMutu}/ai-resolve', [AuditMutuController::class, 'aiResolve'])->name('spmi.audit.ai-resolve');
    Route::post('/spmi/audit/{auditMutu}/transition', [AuditMutuController::class, 'transition'])->name('spmi.audit.transition');
    Route::post('/spmi/audit/batch-transition', [AuditMutuController::class, 'batchTransition'])->name('spmi.audit.batch-transition');
    Route::delete('/spmi/audit/{auditMutu}', [AuditMutuController::class, 'destroy'])->name('spmi.audit.destroy');

    Route::get('/spmi/risk', [RiskRegisterController::class, 'index'])->name('spmi.risk');
    Route::post('/spmi/risk', [RiskRegisterController::class, 'store'])
        ->name('spmi.risk.store')
        ->middleware('throttle:30,1');
    Route::put('/spmi/risk/{riskRegister}', [RiskRegisterController::class, 'update'])
        ->name('spmi.risk.update')
        ->middleware('throttle:30,1');
    Route::delete('/spmi/risk/{riskRegister}', [RiskRegisterController::class, 'destroy'])
        ->name('spmi.risk.destroy')
        ->middleware('throttle:10,1');

    // SPMI Dashboard
    Route::get('/spmi/dashboard', [SpmiDashboardController::class, 'overview'])->name('spmi.dashboard');
    Route::get('/spmi/dashboard/chart-data', [SpmiDashboardController::class, 'chartData'])->name('spmi.dashboard.chart-data');

    // Standar Mutu
    Route::get('/spmi/standar-mutu', [StandarMutuController::class, 'index'])->name('spmi.standar-mutu');
    Route::post('/spmi/standar-mutu', [StandarMutuController::class, 'store'])->name('spmi.standar-mutu.store');
    Route::put('/spmi/standar-mutu/{standarMutu}', [StandarMutuController::class, 'update'])->name('spmi.standar-mutu.update');
    Route::delete('/spmi/standar-mutu/{standarMutu}', [StandarMutuController::class, 'destroy'])->name('spmi.standar-mutu.destroy');

    // CAPA
    Route::get('/spmi/capa', [CapaController::class, 'index'])->name('spmi.capa');
    Route::get('/spmi/capa/{capa}', [CapaController::class, 'show'])->name('spmi.capa.show');
    Route::put('/spmi/capa/{capa}', [CapaController::class, 'update'])->name('spmi.capa.update');
    Route::post('/spmi/capa/{capa}/submit-verification', [CapaController::class, 'submitVerification'])->name('spmi.capa.submit-verification');
    Route::post('/spmi/capa/{capa}/verify', [CapaController::class, 'verify'])->name('spmi.capa.verify');
    Route::get('/spmi/capa/{capa}/timeline', [CapaController::class, 'getTimeline'])->name('spmi.capa.timeline');

    // PPEPP Cycle
    Route::get('/spmi/cycle', [SpmiCycleController::class, 'index'])->name('spmi.cycle');
    Route::post('/spmi/cycle', [SpmiCycleController::class, 'store'])->name('spmi.cycle.store');
    Route::put('/spmi/cycle/{spmiCycle}', [SpmiCycleController::class, 'update'])->name('spmi.cycle.update');
    Route::delete('/spmi/cycle/{spmiCycle}', [SpmiCycleController::class, 'destroy'])->name('spmi.cycle.destroy');

    // EDPS
    Route::get('/spmi/edps', [EdpsController::class, 'index'])->name('spmi.edps');
    Route::post('/spmi/edps', [EdpsController::class, 'store'])->name('spmi.edps.store');
    Route::put('/spmi/edps/{edps}', [EdpsController::class, 'update'])->name('spmi.edps.update');
    Route::delete('/spmi/edps/{edps}', [EdpsController::class, 'destroy'])->name('spmi.edps.destroy');

    // RTM
    Route::get('/spmi/rtm', [RtmController::class, 'index'])->name('spmi.rtm');
    Route::post('/spmi/rtm', [RtmController::class, 'store'])->name('spmi.rtm.store');
    Route::get('/spmi/rtm/{rtm}', [RtmController::class, 'show'])->name('spmi.rtm.show');
    Route::put('/spmi/rtm/{rtm}', [RtmController::class, 'update'])->name('spmi.rtm.update');
    Route::delete('/spmi/rtm/{rtm}', [RtmController::class, 'destroy'])->name('spmi.rtm.destroy');
    // RTM Action Items
    Route::post('/spmi/rtm/{rtm}/action-items', [RtmController::class, 'storeActionItem'])->name('spmi.rtm.action-item.store');
    Route::put('/spmi/rtm/{rtm}/action-items/{rtmActionItem}', [RtmController::class, 'updateActionItem'])->name('spmi.rtm.action-item.update');
    Route::post('/spmi/rtm/{rtm}/action-items/{rtmActionItem}/transition', [RtmController::class, 'transitionActionItem'])->name('spmi.rtm.action-item.transition');
    Route::delete('/spmi/rtm/{rtm}/action-items/{rtmActionItem}', [RtmController::class, 'destroyActionItem'])->name('spmi.rtm.action-item.destroy');

    // Dokumen Mutu
    Route::get('/spmi/dokumen-mutu', [SpmiDokumenController::class, 'index'])->name('spmi.dokumen-mutu');
    Route::post('/spmi/dokumen-mutu', [SpmiDokumenController::class, 'store'])->name('spmi.dokumen-mutu.store');
    Route::put('/spmi/dokumen-mutu/{spmiDokumen}', [SpmiDokumenController::class, 'update'])->name('spmi.dokumen-mutu.update');
    Route::delete('/spmi/dokumen-mutu/{spmiDokumen}', [SpmiDokumenController::class, 'destroy'])->name('spmi.dokumen-mutu.destroy');

    // Survey
    Route::get('/spmi/survey', [SurveySpmiController::class, 'index'])->name('spmi.survey');
    Route::post('/spmi/survey', [SurveySpmiController::class, 'store'])->name('spmi.survey.store');

    // Kurikulum Mapping + RPS Routes
    Route::get('/kurikulum/mapping', [KurikulumMappingController::class, 'index'])->name('kurikulum.mapping');
    Route::post('/kurikulum/mapping/toggle', [KurikulumMappingController::class, 'toggleMapping'])->name('kurikulum.mapping.toggle');

    Route::get('/kurikulum/rps', [RpsController::class, 'index'])->name('kurikulum.rps');
    Route::post('/kurikulum/rps', [RpsController::class, 'store'])->name('kurikulum.rps.store');
    Route::put('/kurikulum/rps/{rp}', [RpsController::class, 'update'])->name('kurikulum.rps.update');
    Route::delete('/kurikulum/rps/{rp}', [RpsController::class, 'destroy'])->name('kurikulum.rps.destroy');

    // Sarpras Routes
    Route::get('/sarpras', [SaranaController::class, 'index'])->name('sarpras');
    Route::post('/sarpras', [SaranaController::class, 'store'])->name('sarpras.store');
    Route::put('/sarpras/{sarana}', [SaranaController::class, 'update'])->name('sarpras.update');
    Route::delete('/sarpras/{sarana}', [SaranaController::class, 'destroy'])->name('sarpras.destroy');

    // Mitra Routes
    Route::get('/mitra', [MitraController::class, 'index'])->name('mitra');
    Route::post('/mitra', [MitraController::class, 'store'])->name('mitra.store');
    Route::put('/mitra/{mitra}', [MitraController::class, 'update'])->name('mitra.update');
    Route::delete('/mitra/{mitra}', [MitraController::class, 'destroy'])->name('mitra.destroy');

    // Kerjasama Routes
    Route::get('/kerjasama', [KerjasamaController::class, 'index'])->name('kerjasama');
    Route::post('/kerjasama', [KerjasamaController::class, 'store'])->name('kerjasama.store');
    Route::put('/kerjasama/{kerjasama}', [KerjasamaController::class, 'update'])->name('kerjasama.update');
    Route::delete('/kerjasama/{kerjasama}', [KerjasamaController::class, 'destroy'])->name('kerjasama.destroy');

    // Keuangan Routes
    Route::get('/keuangan', [KeuanganController::class, 'index'])->name('keuangan');
    Route::post('/keuangan', [KeuanganController::class, 'store'])->name('keuangan.store');
    Route::put('/keuangan/{keuangan}', [KeuanganController::class, 'update'])->name('keuangan.update');
    Route::delete('/keuangan/{keuangan}', [KeuanganController::class, 'destroy'])->name('keuangan.destroy');

    // Alumni Routes
    Route::get('/alumni', [AlumniController::class, 'index'])->name('alumni');
    Route::post('/alumni', [AlumniController::class, 'store'])->name('alumni.store');
    Route::put('/alumni/{alumni}', [AlumniController::class, 'update'])->name('alumni.update');
    Route::delete('/alumni/{alumni}', [AlumniController::class, 'destroy'])->name('alumni.destroy');

    // Tracer Routes
    Route::get('/tracer/kuisioner', [KuisionerTracerController::class, 'index'])->name('tracer.kuisioner');
    Route::post('/tracer/kuisioner', [KuisionerTracerController::class, 'store'])->name('tracer.kuisioner.store');
    Route::put('/tracer/kuisioner/{kuisionerTracer}', [KuisionerTracerController::class, 'update'])->name('tracer.kuisioner.update');
    Route::delete('/tracer/kuisioner/{kuisionerTracer}', [KuisionerTracerController::class, 'destroy'])->name('tracer.kuisioner.destroy');

    Route::get('/tracer/jawaban', [TracerJawabanController::class, 'index'])->name('tracer.jawaban');
    Route::post('/tracer/jawaban', [TracerJawabanController::class, 'store'])->name('tracer.jawaban.store');
    Route::delete('/tracer/jawaban/{tracerJawaban}', [TracerJawabanController::class, 'destroy'])->name('tracer.jawaban.destroy');

    // AI Agent Pages
    Route::get('/peringatan', [PeringatanController::class, 'index'])->name('peringatan');
    Route::post('/peringatan/{id}/read', [PeringatanController::class, 'markAsRead'])->name('peringatan.markRead');
    Route::post('/peringatan/mark-all-read', [PeringatanController::class, 'markAllAsRead'])->name('peringatan.markAllRead');
    Route::post('/peringatan/run', [PeringatanController::class, 'runAgent'])->name('peringatan.run')
        ->middleware('throttle:10,1');

    Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi');
    Route::post('/verifikasi/run', [VerifikasiController::class, 'runAgent'])->name('verifikasi.run')
        ->middleware('throttle:10,1');

    // Generator Dokumen
    Route::get('/generator', [GeneratorController::class, 'index'])->name('generator');
    Route::post('/generator/generate', [GeneratorController::class, 'generate'])->name('generator.generate')
        ->middleware('throttle:10,1');
    Route::get('/generator/download/{id}', [GeneratorController::class, 'download'])->name('generator.download');

    // Prediksi Akreditasi
    Route::get('/prediksi', [PrediksiController::class, 'index'])->name('prediksi');
    Route::post('/prediksi/run', [PrediksiController::class, 'runAgent'])->name('prediksi.run')
        ->middleware('throttle:10,1');
    Route::get('/prediksi/latest', [PrediksiController::class, 'latest'])->name('prediksi.latest');

    // Rekomendasi Agent
    Route::get('/rekomendasi', [RekomendasiController::class, 'index'])->name('rekomendasi');
    Route::post('/rekomendasi/run', [RekomendasiController::class, 'run'])->name('rekomendasi.run')
        ->middleware('throttle:10,1');

    // Integrasi Agent
    Route::get('/integrasi', [IntegrasiController::class, 'index'])->name('integrasi');
    Route::post('/integrasi/run', [IntegrasiController::class, 'run'])->name('integrasi.run')
        ->middleware('throttle:10,1');
    Route::post('/integrasi/sync', [IntegrasiController::class, 'sync'])->name('integrasi.sync')
        ->middleware('throttle:10,1');

    // SINTA Import Routes
    Route::post('/import/sinta/publikasi', [SintaImportController::class, 'importPublikasi'])->name('import.sinta.publikasi')
        ->middleware('throttle:uploads');
    Route::post('/import/sinta/penelitian', [SintaImportController::class, 'importPenelitian'])->name('import.sinta.penelitian')
        ->middleware('throttle:uploads');
    Route::post('/import/sinta/pkm', [SintaImportController::class, 'importPkm'])->name('import.sinta.pkm')
        ->middleware('throttle:uploads');

    // ===== DATA IMPORT ROUTES =====
    Route::middleware('can:data-import.download-template')->group(function () {
        Route::get('/data-import/templates', [DataImportController::class, 'templates'])
            ->name('data-import.templates');
        Route::get('/data-import/templates/download/{type}', [DataImportController::class, 'downloadTemplate'])
            ->name('data-import.templates.download');
    });

    Route::middleware('can:data-import.upload')->group(function () {
        Route::post('/data-import/upload', [DataImportController::class, 'upload'])
            ->name('data-import.upload')
            ->middleware('throttle:uploads');
        Route::post('/data-import/preview', [DataImportController::class, 'preview'])
            ->name('data-import.preview')
            ->middleware('throttle:uploads');
    });

    Route::middleware('can:data-import.view')->group(function () {
        Route::get('/data-import/history', [DataImportController::class, 'history'])
            ->name('data-import.history');
    });

    // ===== RECONCILIATION ROUTES =====
    Route::middleware('can:reconciliation.view')->group(function () {
        Route::get('/reconciliation', [ReconciliationController::class, 'index'])
            ->name('reconciliation.index');
        Route::get('/reconciliation/{id}', [ReconciliationController::class, 'show'])
            ->name('reconciliation.show');
        Route::get('/reconciliation/stats', [ReconciliationController::class, 'stats'])
            ->name('reconciliation.stats');
    });

    Route::middleware('can:reconciliation.approve')->group(function () {
        Route::post('/reconciliation/{id}/approve', [ReconciliationController::class, 'approve'])
            ->name('reconciliation.approve');
        Route::post('/reconciliation/batch-approve', [ReconciliationController::class, 'batchApprove'])
            ->name('reconciliation.batch-approve');
    });

    Route::middleware('can:reconciliation.reject')->group(function () {
        Route::post('/reconciliation/{id}/reject', [ReconciliationController::class, 'reject'])
            ->name('reconciliation.reject');
    });

    // Template Routes
    Route::get('/admin/templates', [TemplateController::class, 'index'])->name('admin.templates.index');
    Route::get('/admin/templates/download/{filename}', [TemplateController::class, 'download'])->name('admin.templates.download');

    // Admin Settings
    Route::get('/admin/settings', [AdminSettingController::class, 'index'])->name('admin.settings');
    Route::post('/admin/settings', [AdminSettingController::class, 'updateMultiple'])->name('admin.settings.update')
        ->middleware('throttle:crud');
    Route::post('/admin/settings/favicon/upload', [AdminSettingController::class, 'uploadFavicon'])->name('admin.settings.favicon.upload')
        ->middleware('throttle:uploads');
    Route::delete('/admin/settings/favicon/remove', [AdminSettingController::class, 'removeFavicon'])->name('admin.settings.favicon.remove')
        ->middleware('throttle:uploads');
    Route::post('/admin/settings/logo/upload', [AdminSettingController::class, 'uploadLogo'])->name('admin.settings.logo.upload')
        ->middleware('throttle:uploads');
    Route::delete('/admin/settings/logo/remove', [AdminSettingController::class, 'removeLogo'])->name('admin.settings.logo.remove');
    Route::delete('/admin/settings/api-key/remove', [AdminSettingController::class, 'removeApiKey'])->name('admin.settings.api-key.remove');
    Route::post('/admin/settings/api-key/test', [AdminSettingController::class, 'testApiKey'])->name('admin.settings.api-key.test');

    // RBAC Management (Super Admin only)
    Route::middleware(['can:admin.view'])->group(function () {
        Route::get('/admin/users/audit', [\App\Http\Controllers\Api\Admin\UserController::class, 'audit'])->name('admin.users.audit');
        Route::get('/admin/users', [\App\Http\Controllers\Api\Admin\UserController::class, 'index'])->name('admin.users.index');
        Route::post('/admin/users', [\App\Http\Controllers\Api\Admin\UserController::class, 'store'])->name('admin.users.store');
        Route::put('/admin/users/{user}', [\App\Http\Controllers\Api\Admin\UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [\App\Http\Controllers\Api\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('/admin/users/{user}/sync-roles', [\App\Http\Controllers\Api\Admin\UserController::class, 'syncRoles'])->name('admin.users.sync-roles');
        Route::get('/admin/users/download-template', [\App\Http\Controllers\Api\Admin\UserController::class, 'downloadTemplate'])->name('admin.users.download-template');
    Route::post('/admin/users/import', [\App\Http\Controllers\Api\Admin\UserController::class, 'import'])->name('admin.users.import')
        ->middleware('throttle:uploads');
    Route::post('/admin/users/import-preview', [\App\Http\Controllers\Api\Admin\UserController::class, 'importPreview'])->name('admin.users.import-preview')
        ->middleware('throttle:uploads');

        Route::get('/admin/roles', [\App\Http\Controllers\Api\Admin\RoleController::class, 'index'])->name('admin.roles.index');
        Route::post('/admin/roles', [\App\Http\Controllers\Api\Admin\RoleController::class, 'store'])->name('admin.roles.store');
        Route::put('/admin/roles/{role}', [\App\Http\Controllers\Api\Admin\RoleController::class, 'update'])->name('admin.roles.update');
        Route::delete('/admin/roles/{role}', [\App\Http\Controllers\Api\Admin\RoleController::class, 'destroy'])->name('admin.roles.destroy');
        Route::post('/admin/roles/{role}/sync-permissions', [\App\Http\Controllers\Api\Admin\RoleController::class, 'syncPermissions'])->name('admin.roles.sync-permissions');

        Route::get('/admin/permissions', [\App\Http\Controllers\Api\Admin\PermissionController::class, 'index'])->name('admin.permissions.index');
    });

    Route::get('/aipt', [AiptController::class, 'index'])->name('aipt.index');

    // Accreditation Management (Lembaga & Instrumen)
    Route::get('/admin/lembaga', [LembagaAkreditasiController::class, 'index'])->name('admin.lembaga.index');
    Route::post('/admin/lembaga', [LembagaAkreditasiController::class, 'store'])->name('admin.lembaga.store');
    Route::put('/admin/lembaga/{lembagaAkreditasi}', [LembagaAkreditasiController::class, 'update'])->name('admin.lembaga.update');
    Route::delete('/admin/lembaga/{lembagaAkreditasi}', [LembagaAkreditasiController::class, 'destroy'])->name('admin.lembaga.destroy');

    Route::get('/admin/instrumen', [InstrumenAkreditasiController::class, 'index'])->name('admin.instrumen.index');
    Route::post('/admin/instrumen', [InstrumenAkreditasiController::class, 'store'])->name('admin.instrumen.store');
    Route::put('/admin/instrumen/{instrumenAkreditasi}', [InstrumenAkreditasiController::class, 'update'])->name('admin.instrumen.update');
    Route::delete('/admin/instrumen/{instrumenAkreditasi}', [InstrumenAkreditasiController::class, 'destroy'])->name('admin.instrumen.destroy');
    Route::post('/admin/instrumen/import-preview', [InstrumenAkreditasiController::class, 'importPreview'])->name('admin.instrumen.import-preview');

    Route::get('/admin/indikator', [IndikatorAkreditasiController::class, 'index'])->name('admin.indikator.index');
    Route::post('/admin/indikator', [IndikatorAkreditasiController::class, 'store'])->name('admin.indikator.store');
    Route::put('/admin/indikator/{indikatorAkreditasi}', [IndikatorAkreditasiController::class, 'update'])->name('admin.indikator.update');
    Route::delete('/admin/indikator/{indikatorAkreditasi}', [IndikatorAkreditasiController::class, 'destroy'])->name('admin.indikator.destroy');

    // Knowledge Base Routes
    Route::get('/admin/knowledge-base', [KnowledgeBaseController::class, 'index'])->name('admin.knowledge-base.index');
    Route::post('/admin/knowledge-base/upload', [KnowledgeBaseController::class, 'upload'])->name('admin.knowledge-base.upload')
        ->middleware('throttle:uploads');
    Route::put('/admin/knowledge-base/{knowledgeBaseDocument}', [KnowledgeBaseController::class, 'update'])->name('admin.knowledge-base.update');
    Route::delete('/admin/knowledge-base/{knowledgeBaseDocument}', [KnowledgeBaseController::class, 'destroy'])->name('admin.knowledge-base.destroy');
    Route::post('/admin/knowledge-base/{knowledgeBaseDocument}/reindex', [KnowledgeBaseController::class, 'reindex'])->name('admin.knowledge-base.reindex');
    Route::get('/admin/knowledge-base/{knowledgeBaseDocument}/chunks', [KnowledgeBaseController::class, 'getChunks'])->name('admin.knowledge-base.chunks');
    Route::put('/admin/knowledge-base/chunks/{knowledgeBaseChunk}', [KnowledgeBaseController::class, 'updateChunk'])->name('admin.knowledge-base.chunks.update');

    // RKAT
    Route::prefix('rkat')->name('rkat.')->group(function () {
        Route::get('/', [RkatController::class, 'index'])->name('index')->can('rkat.view');
        Route::post('/', [RkatController::class, 'store'])->name('store')->can('rkat.create');
        Route::get('/create', [RkatController::class, 'create'])->name('create')->can('rkat.create');
        Route::get('/{id}', [RkatController::class, 'show'])->name('show')->can('rkat.view');
        Route::post('/{id}/approve', [RkatController::class, 'approve'])->name('approve')->can('rkat.approve');
        Route::get('/pagu/manage', [RkatController::class, 'paguIndex'])->name('pagu')->can('rkat.configure');
        Route::post('/pagu/store', [RkatController::class, 'paguStore'])->name('pagu.store')->can('rkat.configure');
    });

    // IKU & Cascading
    Route::prefix('iku')->name('iku.')->group(function () {
        Route::get('/', [IkuController::class, 'index'])->name('index')->can('iku.view');
        Route::post('/', [IkuController::class, 'store'])->name('store')->can('iku.create');
        Route::put('/{iku}', [IkuController::class, 'update'])->name('update')->can('iku.edit');
        Route::delete('/{iku}', [IkuController::class, 'destroy'])->name('destroy')->can('iku.delete');
        Route::get('/cascading', [CascadingIkuController::class, 'index'])->name('cascading')->can('cascading.view');
        Route::post('/cascading/store', [CascadingIkuController::class, 'store'])->name('cascading.store')->can('cascading.create');
        Route::post('/cascading/{cascading}/capaian', [CascadingIkuController::class, 'updateCapaian'])->name('cascading.capaian')->can('cascading.edit');
    });
});

require __DIR__.'/auth.php';
