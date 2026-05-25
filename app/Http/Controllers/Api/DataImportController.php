<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DataImportController extends Controller
{
    public function templates()
    {
        $templates = [
            ['type' => 'dosen', 'label' => 'Template Data Dosen', 'fields' => ['nidn', 'nama', 'prodi']],
            ['type' => 'mahasiswa', 'label' => 'Template Data Mahasiswa', 'fields' => ['nim', 'nama', 'prodi']],
            ['type' => 'mata_kuliah', 'label' => 'Template Mata Kuliah', 'fields' => ['kode_mk', 'nama_mk', 'sks']],
        ];

        return inertia('DataImport/Index', ['templates' => $templates]);
    }

    public function downloadTemplate(string $type)
    {
        $headers = match ($type) {
            'dosen' => ['NIDN', 'Nama Depan', 'Nama Belakang', 'Prodi', 'Status'],
            'mahasiswa' => ['NIM', 'Nama', 'Prodi', 'Angkatan'],
            'mata_kuliah' => ['Kode MK', 'Nama MK', 'SKS', 'Semester'],
            default => abort(404, 'Template tidak ditemukan'),
        };

        $filename = "template_import_{$type}.csv";
        $handle = fopen('php://temp', 'w');
        fputcsv($handle, $headers);
        rewind($handle);

        return response()->streamDownload(function () use ($handle) {
            rewind($handle);
            fpassthru($handle);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'type' => 'required|in:dosen,mahasiswa,mata_kuliah',
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->store('imports/' . $request->type);

        return redirect()->route('data-import.history')->with('success', 'File berhasil diupload. Proses impor akan dijalankan.');
    }

    public function history()
    {
        $imports = collect([]);

        return inertia('DataImport/History', ['imports' => $imports]);
    }
}
