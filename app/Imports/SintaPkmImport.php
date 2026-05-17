<?php

namespace App\Imports;

use App\Models\Pkm;
use App\Models\Dosen;
use App\Models\PeriodeAkademik;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class SintaPkmImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    use Importable;

    public function model(array $row)
    {
        $nidn = $row['nidn'] ?? $row['author_id'] ?? null;
        $title = $row['title'] ?? $row['judul'] ?? $row['pkm_title'] ?? null;
        $year = $row['year'] ?? $row['tahun'] ?? date('Y');
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

        // SYNC LOGIC: Update existing PkM record or create new one
        return Pkm::updateOrCreate(
            [
                'dosen_id' => $dosen->id,
                'judul_pkm' => $title,
            ],
            [
                'prodi_id'         => $dosen->prodi_id,
                'periode_id'       => $periode ? $periode->id : null,
                'jenis_pkm'        => $row['scheme'] ?? $row['skema'] ?? $row['jenis'] ?? 'Pengabdian Masyarakat',
                'sumber_dana'      => $row['source'] ?? $row['sumber_dana'] ?? $row['funding'] ?? 'Internal/Mandiri',
                'jumlah_dana'      => $row['amount'] ?? $row['jumlah'] ?? 0,
                'tahun_pelaksanaan'=> $year,
                'is_verified'      => true,
            ]
        );
    }
}
