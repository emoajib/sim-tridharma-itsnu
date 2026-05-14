<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\FakultasController;
use App\Http\Controllers\Api\ProdiController;
use App\Http\Controllers\Api\DosenController;
use App\Http\Controllers\Api\MataKuliahController;
use App\Http\Controllers\Api\KurikulumController;
use App\Http\Controllers\Api\CplController;
use App\Http\Controllers\Api\PeriodeAkademikController;
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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
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
});

require __DIR__.'/auth.php';
