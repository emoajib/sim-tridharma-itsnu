<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Prodi;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DosenSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/8444.xls');

        if (! file_exists($path)) {
            echo "⚠️  File 8444.xls not found in database/seeders/data/, skipping DosenSeeder\n";

            return;
        }

        $prodiList = Prodi::all();
        $prodiMap = [];
        foreach ($prodiList as $p) {
            $prodiMap[$p->kode_prodi] = $p->id;
            $prodiMap[$p->nama_prodi] = $p->id;
        }
        $prodiMap['S1 Teknologi Industri'] = $prodiMap['S1 Teknik Industri'] ?? null;

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $headerRow = null;
        $colMap = [];
        foreach ($rows as $rowIndex => $row) {
            $row = array_map('trim', array_map('strval', $row));
            if (in_array('NIDN', $row, true)) {
                $headerRow = $rowIndex;
                $colMap = [
                    'nidn' => array_search('NIDN', $row),
                    'nama' => array_search('NAMA', $row),
                    'prodi' => array_search('PRODI', $row),
                    'pendidikan' => array_search('PENDIDIKAN TERAKHIR', $row),
                    'jabatan' => array_search('JABATAN FUNGSIONAL', $row),
                    'gelar_depan' => array_search('GELAR DEPAN', $row),
                    'gelar_belakang' => array_search('GELAR BELAKANG', $row),
                ];
                break;
            }
        }

        if (! $headerRow) {
            echo "⚠️  Could not find NIDN header in 8444.xls\n";

            return;
        }

        $imported = 0;
        for ($i = $headerRow + 1; $i < count($rows); $i++) {
            $row = array_map('trim', array_map('strval', $rows[$i]));
            $nidn = $row[$colMap['nidn']] ?? '';
            if (empty($nidn)) {
                continue;
            }

            $nama = $row[$colMap['nama']] ?? '';
            $prodiName = $row[$colMap['prodi']] ?? '';
            $pendidikan = $row[$colMap['pendidikan']] ?? '';
            $jabatan = $row[$colMap['jabatan']] ?? '';
            $gelarDepan = $row[$colMap['gelar_depan']] ?? '';
            $gelarBelakang = $row[$colMap['gelar_belakang']] ?? '';

            $prodiId = $prodiMap[$prodiName] ?? null;
            [$namaDepan, $namaBelakang] = $this->splitNama($nama);

            Dosen::firstOrCreate(
                ['nidn' => $nidn],
                [
                    'nama_depan' => $namaDepan,
                    'nama_belakang' => $namaBelakang,
                    'gelar_depan' => $gelarDepan ?: null,
                    'gelar_belakang' => $gelarBelakang ?: null,
                    'prodi_id' => $prodiId,
                    'pendidikan_terakhir' => $pendidikan ?: null,
                    'jabatan_fungsional' => $jabatan ?: null,
                    'is_active' => true,
                    'status_aktivitas' => 'aktif',
                ]
            );

            $imported++;
        }

        echo "✅ {$imported} data dosen dari 8444.xls\n";
    }

    private function splitNama(string $fullName): array
    {
        $parts = array_values(array_filter(explode(' ', $fullName), fn ($p) => $p !== ''));
        $count = count($parts);

        if ($count === 0) {
            return ['', ''];
        }
        if ($count === 1) {
            return [$parts[0], ''];
        }
        if ($count === 2) {
            return [$parts[0], $parts[1]];
        }

        $namaDepan = implode(' ', array_slice($parts, 0, $count - 2));
        $namaBelakang = implode(' ', array_slice($parts, -2));

        return [$namaDepan, $namaBelakang];
    }
}
