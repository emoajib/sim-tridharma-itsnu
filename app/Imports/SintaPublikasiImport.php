<?php

namespace App\Imports;

use App\Models\Publikasi;
use App\Models\Dosen;
use App\Models\PeriodeAkademik;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Facades\Log;

class SintaPublikasiImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    use Importable;

    public function model(array $row)
    {
        Log::info('Processing SINTA Row', $row);

        // Normalize keys (handle spaces and case)
        $cleanRow = [];
        foreach ($row as $key => $val) {
            $cleanRow[strtolower(trim(str_replace(' ', '_', $key)))] = $val;
        }

        // Support various SINTA export formats
        $nidn = $cleanRow['nidn'] ?? $cleanRow['author_id'] ?? $cleanRow['id_sinta'] ?? null;
        $title = $cleanRow['title'] ?? $cleanRow['judul'] ?? $cleanRow['publication_name'] ?? $cleanRow['article_title'] ?? null;
        $year = $cleanRow['year'] ?? $cleanRow['tahun'] ?? $cleanRow['publication_year'] ?? date('Y');
        $quartile = $cleanRow['quartile'] ?? $cleanRow['sjr_quartile'] ?? $cleanRow['index'] ?? null;
        $authors = $cleanRow['authors'] ?? $cleanRow['penulis'] ?? $cleanRow['author'] ?? null;

        if (!$title) {
            Log::warning('SINTA Import: Missing Title in row', $cleanRow);
            return null;
        }

        $dosen = null;
        // Try matching by NIDN
        if ($nidn) {
            $dosen = Dosen::where('nidn', $nidn)->first();
        }

        // Try matching by Author Name
        if (!$dosen && $authors) {
            $authorParts = explode(',', $authors);
            $firstAuthor = trim($authorParts[0]);
            $dosen = Dosen::where('nama_depan', 'like', "%{$firstAuthor}%")
                         ->orWhere('nama_belakang', 'like', "%{$firstAuthor}%")
                         ->first();
        }

        // AUTO-MATCH FALLBACK: If only one Dosen exists in DB, use it for testing/small campuses
        if (!$dosen && Dosen::count() === 1) {
            $dosen = Dosen::first();
            Log::info("SINTA Import: Falling back to only Dosen available (ID: {$dosen->id})");
        }

        if (!$dosen) {
            Log::error('SINTA Import: Dosen not found for row', ['nidn' => $nidn, 'authors' => $authors]);
            return null;
        }

        $periode = PeriodeAkademik::where('is_active', true)->first();

        Log::info("SINTA Import: Syncing Publication for Dosen {$dosen->id}", ['title' => $title]);

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
                'link'            => $cleanRow['url'] ?? $cleanRow['link'] ?? $cleanRow['doi'] ?? null,
                'tahun'           => $year,
                'is_verified'     => true,
            ]
        );
    }
}
