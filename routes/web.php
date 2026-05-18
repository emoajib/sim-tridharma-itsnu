<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\FakultasController;
use App\Http\Controllers\Api\ProdiController;
use App\Http\Controllers\Api\DosenController;
use App\Http\Controllers\Api\MataKuliahController;
use App\Http\Controllers\Api\KurikulumController;
use App\Http\Controllers\Api\CplController;
use App\Http\Controllers\Api\PeriodeAkademikController;
use App\Http\Controllers\Api\KegiatanPendidikanController;
use App\Http\Controllers\Api\PenelitianController;
use App\Http\Controllers\Api\PublikasiController;
use App\Http\Controllers\Api\PkmController;
use App\Http\Controllers\Api\PenunjangController;
use App\Http\Controllers\Api\PortofolioController;
use App\Http\Controllers\Api\BkdController;
use App\Http\Controllers\Api\DokumenBuktiController;
use App\Http\Controllers\Api\MahasiswaBimbinganController;
use App\Http\Controllers\Api\SaranaController;
use App\Http\Controllers\Api\MitraController;
use App\Http\Controllers\Api\KerjasamaController;
use App\Http\Controllers\Api\KeuanganController;
use App\Http\Controllers\Api\AlumniController;
use App\Http\Controllers\Api\KuisionerTracerController;
use App\Http\Controllers\Api\TracerJawabanController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\RoleSwitchController;
use App\Http\Controllers\Api\AiptController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\KnowledgeBaseController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/role/switch', [RoleSwitchController::class, 'switch'])->name('role.switch');

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
    Route::get('/spmi/audit', [\App\Http\Controllers\Api\AuditMutuController::class, 'index'])->name('spmi.audit');
    Route::post('/spmi/audit', [\App\Http\Controllers\Api\AuditMutuController::class, 'store'])->name('spmi.audit.store');
    Route::put('/spmi/audit/{auditMutu}', [\App\Http\Controllers\Api\AuditMutuController::class, 'update'])->name('spmi.audit.update');
    Route::delete('/spmi/audit/{auditMutu}', [\App\Http\Controllers\Api\AuditMutuController::class, 'destroy'])->name('spmi.audit.destroy');

    Route::get('/spmi/risk', [\App\Http\Controllers\Api\RiskRegisterController::class, 'index'])->name('spmi.risk');
    Route::post('/spmi/risk', [\App\Http\Controllers\Api\RiskRegisterController::class, 'store'])->name('spmi.risk.store');
    Route::put('/spmi/risk/{riskRegister}', [\App\Http\Controllers\Api\RiskRegisterController::class, 'update'])->name('spmi.risk.update');
    Route::delete('/spmi/risk/{riskRegister}', [\App\Http\Controllers\Api\RiskRegisterController::class, 'destroy'])->name('spmi.risk.destroy');

    // Kurikulum Mapping + RPS Routes
    Route::get('/kurikulum/mapping', [\App\Http\Controllers\Api\KurikulumMappingController::class, 'index'])->name('kurikulum.mapping');
    Route::post('/kurikulum/mapping/toggle', [\App\Http\Controllers\Api\KurikulumMappingController::class, 'toggleMapping'])->name('kurikulum.mapping.toggle');

    Route::get('/kurikulum/rps', [\App\Http\Controllers\Api\RpsController::class, 'index'])->name('kurikulum.rps');
    Route::post('/kurikulum/rps', [\App\Http\Controllers\Api\RpsController::class, 'store'])->name('kurikulum.rps.store');
    Route::put('/kurikulum/rps/{rp}', [\App\Http\Controllers\Api\RpsController::class, 'update'])->name('kurikulum.rps.update');
    Route::delete('/kurikulum/rps/{rp}', [\App\Http\Controllers\Api\RpsController::class, 'destroy'])->name('kurikulum.rps.destroy');

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
    Route::get('/peringatan', [\App\Http\Controllers\Api\PeringatanController::class, 'index'])->name('peringatan');
    Route::post('/peringatan/{id}/read', [\App\Http\Controllers\Api\PeringatanController::class, 'markAsRead'])->name('peringatan.markRead');
    Route::post('/peringatan/mark-all-read', [\App\Http\Controllers\Api\PeringatanController::class, 'markAllAsRead'])->name('peringatan.markAllRead');
    Route::post('/peringatan/run', [\App\Http\Controllers\Api\PeringatanController::class, 'runAgent'])->name('peringatan.run');

    Route::get('/verifikasi', [\App\Http\Controllers\Api\VerifikasiController::class, 'index'])->name('verifikasi');
    Route::post('/verifikasi/run', [\App\Http\Controllers\Api\VerifikasiController::class, 'runAgent'])->name('verifikasi.run');

    // Generator Dokumen
    Route::get('/generator', [\App\Http\Controllers\Api\GeneratorController::class, 'index'])->name('generator');
    Route::post('/generator/generate', [\App\Http\Controllers\Api\GeneratorController::class, 'generate'])->name('generator.generate');

    // SINTA Import Routes
    Route::post('/import/sinta/publikasi', [\App\Http\Controllers\Api\SintaImportController::class, 'importPublikasi'])->name('import.sinta.publikasi');
    Route::post('/import/sinta/penelitian', [\App\Http\Controllers\Api\SintaImportController::class, 'importPenelitian'])->name('import.sinta.penelitian');
    Route::post('/import/sinta/pkm', [\App\Http\Controllers\Api\SintaImportController::class, 'importPkm'])->name('import.sinta.pkm');

    // Template Routes
    Route::get('/admin/templates', [TemplateController::class, 'index'])->name('admin.templates.index');
    Route::get('/admin/templates/download/{filename}', [TemplateController::class, 'download'])->name('admin.templates.download');

    // Admin Settings
    Route::get('/admin/settings', [\App\Http\Controllers\Api\AdminSettingController::class, 'index'])->name('admin.settings');
    Route::post('/admin/settings', [\App\Http\Controllers\Api\AdminSettingController::class, 'updateMultiple'])->name('admin.settings.update');
    Route::get('/aipt', [AiptController::class, 'index'])->name('aipt.index');

    // Accreditation Management (Lembaga & Instrumen)
    Route::get('/admin/lembaga', [\App\Http\Controllers\Api\LembagaAkreditasiController::class, 'index'])->name('admin.lembaga.index');
    Route::post('/admin/lembaga', [\App\Http\Controllers\Api\LembagaAkreditasiController::class, 'store'])->name('admin.lembaga.store');
    Route::put('/admin/lembaga/{lembagaAkreditasi}', [\App\Http\Controllers\Api\LembagaAkreditasiController::class, 'update'])->name('admin.lembaga.update');
    Route::delete('/admin/lembaga/{lembagaAkreditasi}', [\App\Http\Controllers\Api\LembagaAkreditasiController::class, 'destroy'])->name('admin.lembaga.destroy');

    Route::get('/admin/instrumen', [\App\Http\Controllers\Api\InstrumenAkreditasiController::class, 'index'])->name('admin.instrumen.index');
    Route::post('/admin/instrumen', [\App\Http\Controllers\Api\InstrumenAkreditasiController::class, 'store'])->name('admin.instrumen.store');
    Route::put('/admin/instrumen/{instrumenAkreditasi}', [\App\Http\Controllers\Api\InstrumenAkreditasiController::class, 'update'])->name('admin.instrumen.update');
    Route::delete('/admin/instrumen/{instrumenAkreditasi}', [\App\Http\Controllers\Api\InstrumenAkreditasiController::class, 'destroy'])->name('admin.instrumen.destroy');
    Route::post('/admin/instrumen/import-preview', [\App\Http\Controllers\Api\InstrumenAkreditasiController::class, 'importPreview'])->name('admin.instrumen.import-preview');

    Route::get('/admin/indikator', [\App\Http\Controllers\Api\IndikatorAkreditasiController::class, 'index'])->name('admin.indikator.index');
    Route::post('/admin/indikator', [\App\Http\Controllers\Api\IndikatorAkreditasiController::class, 'store'])->name('admin.indikator.store');
    Route::put('/admin/indikator/{indikatorAkreditasi}', [\App\Http\Controllers\Api\IndikatorAkreditasiController::class, 'update'])->name('admin.indikator.update');
    Route::delete('/admin/indikator/{indikatorAkreditasi}', [\App\Http\Controllers\Api\IndikatorAkreditasiController::class, 'destroy'])->name('admin.indikator.destroy');

    // Knowledge Base Routes
    Route::get('/admin/knowledge-base', [KnowledgeBaseController::class, 'index'])->name('admin.knowledge-base.index');
    Route::post('/admin/knowledge-base/upload', [KnowledgeBaseController::class, 'upload'])->name('admin.knowledge-base.upload');
    Route::delete('/admin/knowledge-base/{knowledgeBaseDocument}', [KnowledgeBaseController::class, 'destroy'])->name('admin.knowledge-base.destroy');
    Route::post('/admin/knowledge-base/{knowledgeBaseDocument}/reindex', [KnowledgeBaseController::class, 'reindex'])->name('admin.knowledge-base.reindex');
});

require __DIR__.'/auth.php';
