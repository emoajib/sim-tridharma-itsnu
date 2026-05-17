<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\SintaPublikasiImport;
use App\Imports\SintaPenelitianImport;
use App\Imports\SintaPkmImport;
use App\Imports\SintaDosenImport;
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

    public function importPublikasi(Request $request)
    {
        $request->validate(['file' => 'required']);

        try {
            // STEP 1: Sync Dosen Profiles
            $this->universalImport(new SintaDosenImport, $request->file('file'));
            // STEP 2: Sync Publications
            $this->universalImport(new SintaPublikasiImport, $request->file('file'));
            
            return redirect()->back()->with('success', 'Data Profil Dosen & Publikasi SINTA berhasil disinkronkan.');
        } catch (\Exception $e) {
            Log::error('SINTA Import error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal sinkronisasi: ' . $e->getMessage());
        }
    }

    public function importPenelitian(Request $request)
    {
        $request->validate(['file' => 'required']);

        try {
            $this->universalImport(new SintaDosenImport, $request->file('file'));
            $this->universalImport(new SintaPenelitianImport, $request->file('file'));
            
            return redirect()->back()->with('success', 'Data Profil Dosen & Penelitian SINTA berhasil disinkronkan.');
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
            $this->universalImport(new SintaPkmImport, $request->file('file'));
            
            return redirect()->back()->with('success', 'Data Profil Dosen & PkM SINTA berhasil disinkronkan.');
        } catch (\Exception $e) {
            Log::error('SINTA Import error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal sinkronisasi: ' . $e->getMessage());
        }
    }
}
