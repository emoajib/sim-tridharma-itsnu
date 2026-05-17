<?php

namespace App\Imports;

use App\Models\Publikasi;
use App\Models\Dosen;
use App\Models\PeriodeAkademik;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class SintaPublikasiImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    use Importable;

    public function model(array $row)
    {
        // Flexible column mapping based on SINTA export patterns (XLS/XLSX)
        $nidn = $row['nidn'] ?? $row['author_id'] ?? $row['id_sinta'] ?? null;
        $title = $row['title'] ?? $row['judul'] ?? $row['publication_name'] ?? $row['article_title'] ?? null;
        $year = $row['year'] ?? $row['tahun'] ?? $row['publication_year'] ?? date('Y');
        $quartile = $row['quartile'] ?? $row['sjr_quartile'] ?? $row['index'] ?? null;
        $authors = $row['authors'] ?? $row['penulis'] ?? $row['author'] ?? null;

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

        // SYNC LOGIC: Update if exists (by Dosen & Title), otherwise Create
        return Publikasi::updateOrCreate(
            [
                'dosen_id' => $dosen->id,
                'judul_publikasi' => $title,
            ],
            [
                'prodi_id'        => $dosen->prodi_id,
                'periode_id'      => $periode ? $periode->id : null,
                'jenis_publikasi' => $quartile ? "Jurnal Terindeks ($quartile)" : "Jurnal Nasional",
                'tingkat'         => ($quartile && (str_contains(strtoupper($quartile), 'Q') || str_contains(strtoupper($quartile), 'SCOPUS'))) ? 'Internasional' : 'Nasional',
                'link'            => $row['url'] ?? $row['link'] ?? $row['doi'] ?? null,
                'tahun'           => $year,
                'is_verified'     => true,
            ]
        );
    }
}
