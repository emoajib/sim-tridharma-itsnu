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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('master-data/summary', [MasterDataController::class, 'summary']);

    Route::apiResource('fakultas', FakultasController::class);
    Route::apiResource('prodi', ProdiController::class);
    Route::apiResource('dosen', DosenController::class);
    Route::apiResource('mata-kuliah', MataKuliahController::class);
    Route::apiResource('kurikulum', KurikulumController::class);
    Route::apiResource('cpl', CplController::class);
    Route::apiResource('periode-akademik', PeriodeAkademikController::class);

    Route::apiResource('kegiatan-pendidikan', KegiatanPendidikanController::class);
    Route::apiResource('penelitian', PenelitianController::class);
    Route::apiResource('publikasi', PublikasiController::class);
    Route::apiResource('pkm', PkmController::class);
    Route::apiResource('penunjang', PenunjangController::class);
});
