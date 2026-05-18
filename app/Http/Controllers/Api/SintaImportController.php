<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\SintaPublikasiImport;
use App\Imports\SintaPenelitianImport;
use App\Imports\SintaPkmImport;
use App\Imports\SintaDosenImport;
use App\Models\Penelitian;
use App\Models\Publikasi;
use App\Models\Pkm;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class SintaImportController extends Controller
{
    /**
     * Universal Brute-Force Parser for SINTA Exports
     * Tries multiple readers to handle SINTA's unpredictable XLS/HTML/XML output
     */
    private function universalImport($importClass, $file)
    {
        $readers = [
            null, // Default (Auto-detect)
            \Maatwebsite\Excel\Excel::XLSX,
            \Maatwebsite\Excel\Excel::XLS,
            \Maatwebsite\Excel\Excel::HTML,
            \Maatwebsite\Excel\Excel::CSV,
            \Maatwebsite\Excel\Excel::TSV,
        ];

        $lastException = null;

        foreach ($readers as $reader) {
            try {
                return Excel::import($importClass, $file, null, $reader);
            } catch (\Exception $e) {
                $lastException = $e;
                continue;
            }
        }

        throw $lastException;
    }

    public function importPenelitian(Request $request)
    {
        $request->validate(['file' => 'required']);

        try {
            $this->universalImport(new SintaDosenImport, $request->file('file'));

            $before = Penelitian::count();
            $this->universalImport(new SintaPenelitianImport, $request->file('file'));
            $imported = Penelitian::count() - $before;

            if ($imported > 0) {
                return redirect()->back()->with('success', "Data Profil Dosen & {$imported} Penelitian SINTA berhasil disinkronkan.");
            } else {
                return redirect()->back()->with('warning', 'Profil Dosen berhasil diimpor. Tidak ditemukan data Penelitian dalam file ini — gunakan export Penelitian dari SINTA.');
            }
        } catch (\Exception $e) {
            Log::error('SINTA Import error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal sinkronisasi: ' . $e->getMessage());
        }
    }

    public function importPublikasi(Request $request)
    {
        $request->validate(['file' => 'required']);

        try {
            $this->universalImport(new SintaDosenImport, $request->file('file'));

            $before = Publikasi::count();
            $this->universalImport(new SintaPublikasiImport, $request->file('file'));
            $imported = Publikasi::count() - $before;

            if ($imported > 0) {
                return redirect()->back()->with('success', "Data Profil Dosen & {$imported} Publikasi SINTA berhasil disinkronkan.");
            } else {
                return redirect()->back()->with('warning', 'Profil Dosen berhasil diimpor. Tidak ditemukan data Publikasi dalam file ini — gunakan export Publikasi dari SINTA.');
            }
        } catch (\Exception $e) {
            Log::error('SINTA Import error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal sinkronisasi: ' . $e->getMessage());
        }
    }

    public function importPkm(Request $request)
    {
        $request->validate(['file' => 'required']);

        try {
            $this->universalImport(new SintaDosenImport, $request->file('file'));

            $before = Pkm::count();
            $this->universalImport(new SintaPkmImport, $request->file('file'));
            $imported = Pkm::count() - $before;

            if ($imported > 0) {
                return redirect()->back()->with('success', "Data Profil Dosen & {$imported} PkM SINTA berhasil disinkronkan.");
            } else {
                return redirect()->back()->with('warning', 'Profil Dosen berhasil diimpor. Tidak ditemukan data PkM dalam file ini — gunakan export PkM dari SINTA.');
            }
        } catch (\Exception $e) {
            Log::error('SINTA Import error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal sinkronisasi: ' . $e->getMessage());
        }
    }

}
