<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\SintaPublikasiImport;
use App\Imports\SintaPenelitianImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SintaImportController extends Controller
{
    public function importPublikasi(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new SintaPublikasiImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data publikasi SINTA berhasil diimpor.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    public function importPenelitian(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new SintaPenelitianImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data penelitian SINTA berhasil diimpor.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }
}
