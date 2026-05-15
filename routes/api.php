<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FakultasController;
use App\Http\Controllers\Api\ProdiController;
use App\Http\Controllers\Api\DosenController;
use App\Http\Controllers\Api\MataKuliahController;
use App\Http\Controllers\Api\KurikulumController;
use App\Http\Controllers\Api\CplController;
use App\Http\Controllers\Api\PeriodeAkademikController;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\KegiatanPendidikanController;
use App\Http\Controllers\Api\PenelitianController;
use App\Http\Controllers\Api\PublikasiController;
use App\Http\Controllers\Api\PkmController;
use App\Http\Controllers\Api\PenunjangController;
use App\Http\Controllers\Api\BkdController;
use App\Http\Controllers\Api\DokumenBuktiController;
use App\Http\Controllers\Api\MahasiswaBimbinganController;
use App\Http\Controllers\Api\RoleSwitchController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('role/switch', [RoleSwitchController::class, 'switch']);
    Route::get('role/list', [RoleSwitchController::class, 'roles']);

    Route::get('master-data/summary', [MasterDataController::class, 'summary']);

    Route::apiResource('fakultas', FakultasController::class)->except('show');
    Route::apiResource('prodi', ProdiController::class)->except('show');
    Route::apiResource('dosen', DosenController::class)->except('show');
    Route::apiResource('mata-kuliah', MataKuliahController::class)->except('show');
    Route::apiResource('kurikulum', KurikulumController::class)->except('show');
    Route::apiResource('cpl', CplController::class)->except('show');
    Route::apiResource('periode-akademik', PeriodeAkademikController::class)->except('show');

    Route::apiResource('kegiatan-pendidikan', KegiatanPendidikanController::class)->except('show');
    Route::apiResource('penelitian', PenelitianController::class)->except('show');
    Route::apiResource('publikasi', PublikasiController::class)->except('show');
    Route::apiResource('pkm', PkmController::class)->except('show');
    Route::apiResource('penunjang', PenunjangController::class)->except('show');

    Route::apiResource('bkd', BkdController::class)->except('show');
    Route::apiResource('dokumen', DokumenBuktiController::class)->except('show');
    Route::apiResource('bimbingan', MahasiswaBimbinganController::class)->except('show');

    // AI Agent Routes
    Route::post('agents/{agent}/run', [\App\Http\Controllers\Api\AgentController::class, 'run']);
    Route::get('agents/status', [\App\Http\Controllers\Api\AgentController::class, 'status']);
});
