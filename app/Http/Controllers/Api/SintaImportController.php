<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sinta\ImportRequest;
use App\Services\Sinta\SintaImportService;
use Illuminate\Support\Facades\Log;

class SintaImportController extends Controller
{
    public function __construct(
        protected SintaImportService $sintaImport,
    ) {}

    public function importPenelitian(ImportRequest $request)
    {
        try {
            $result = $this->sintaImport->importPenelitian($request->file('file'));

            if ($result['imported'] > 0) {
                return redirect()->back()->with('success', "Data Profil Dosen & {$result['imported']} Penelitian SINTA berhasil disinkronkan.");
            }

            return redirect()->back()->with('warning', 'Profil Dosen berhasil diimpor. Tidak ditemukan data Penelitian dalam file ini — gunakan export Penelitian dari SINTA.');
        } catch (\Exception $e) {
            Log::error('SINTA Import error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal sinkronisasi: '.$e->getMessage());
        }
    }

    public function importPublikasi(ImportRequest $request)
    {
        try {
            $result = $this->sintaImport->importPublikasi($request->file('file'));

            if ($result['imported'] > 0) {
                return redirect()->back()->with('success', "Data Profil Dosen & {$result['imported']} Publikasi SINTA berhasil disinkronkan.");
            }

            return redirect()->back()->with('warning', 'Profil Dosen berhasil diimpor. Tidak ditemukan data Publikasi dalam file ini — gunakan export Publikasi dari SINTA.');
        } catch (\Exception $e) {
            Log::error('SINTA Import error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal sinkronisasi: '.$e->getMessage());
        }
    }

    public function importPkm(ImportRequest $request)
    {
        try {
            $result = $this->sintaImport->importPkm($request->file('file'));

            if ($result['imported'] > 0) {
                return redirect()->back()->with('success', "Data Profil Dosen & {$result['imported']} PkM SINTA berhasil disinkronkan.");
            }

            return redirect()->back()->with('warning', 'Profil Dosen berhasil diimpor. Tidak ditemukan data PkM dalam file ini — gunakan export PkM dari SINTA.');
        } catch (\Exception $e) {
            Log::error('SINTA Import error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal sinkronisasi: '.$e->getMessage());
        }
    }
}
