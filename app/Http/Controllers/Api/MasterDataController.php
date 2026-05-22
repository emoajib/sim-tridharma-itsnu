<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cpl;
use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\Kurikulum;
use App\Models\MataKuliah;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;

class MasterDataController extends Controller
{
    public function summary()
    {
        return response()->json([
            'fakultas_count' => Fakultas::count(),
            'prodi_count' => Prodi::count(),
            'dosen_count' => Dosen::count(),
            'mata_kuliah_count' => MataKuliah::count(),
            'kurikulum_count' => Kurikulum::count(),
            'cpl_count' => Cpl::count(),
            'periode_count' => PeriodeAkademik::count(),
        ]);
    }
}
