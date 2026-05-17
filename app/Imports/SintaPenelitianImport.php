<?php

namespace App\Imports;

use App\Models\Penelitian;
use App\Models\Dosen;
use App\Models\PeriodeAkademik;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class SintaPenelitianImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    use Importable;

    public function model(array $row)
    {
        $nidn = $row['nidn'] ?? $row['author_id'] ?? null;
        $title = $row['title'] ?? $row['judul'] ?? $row['research_title'] ?? null;
        $year = $row['year'] ?? $row['tahun'] ?? $row['research_year'] ?? date('Y');
        $authors = $row['authors'] ?? $row['penulis'] ?? null;

        if (!$title) return null;

        $dosen = null;
        if ($nidn) {
            $dosen = Dosen::where('nidn', $nidn)->first();
        }

        if (!$dosen && $authors) {
            $authorParts = explode(',', $authors);
            $firstAuthor = trim($authorParts[0]);
            $dosen = Dosen::where('nama_depan', 'like', "%{$firstAuthor}%")
                         ->orWhere('nama_belakang', 'like', "%{$firstAuthor}%")
                         ->first();
        }

        if (!$dosen) return null;

        $periode = PeriodeAkademik::where('is_active', true)->first();

        // SYNC LOGIC: Update existing research record or create new one
        return Penelitian::updateOrCreate(
            [
                'dosen_id' => $dosen->id,
                'judul_penelitian' => $title,
            ],
            [
                'prodi_id'         => $dosen->prodi_id,
                'periode_id'       => $periode ? $periode->id : null,
                'jenis_penelitian' => $row['scheme'] ?? $row['skema'] ?? $row['jenis'] ?? 'Penelitian Terapan',
                'sumber_dana'      => $row['source'] ?? $row['sumber_dana'] ?? $row['funding'] ?? 'Internal/Mandiri',
                'jumlah_dana'      => $row['amount'] ?? $row['jumlah'] ?? $row['total_funding'] ?? 0,
                'tahun_pelaksanaan'=> $year,
                'is_verified'      => true,
            ]
        );
    }
}
