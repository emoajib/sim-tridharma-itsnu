<?php

namespace App\Console\Commands;

use App\Models\Dosen;
use App\Models\Prodi;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportDosenExcel extends Command
{
    protected $signature = 'dosen:import {file=8444.xls}';

    protected $description = 'Import dosen from SINTA export XLS file';

    public function handle(): int
    {
        $path = base_path($this->argument('file'));

        if (! file_exists($path)) {
            $this->error("File not found: {$path}");

            return Command::FAILURE;
        }

        $prodiMap = $this->buildProdiMap();

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $headerRow = null;
        $colMap = [];
        $imported = 0;
        $skipped = 0;

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
            $this->error('Could not find header row (NIDN)');

            return Command::FAILURE;
        }

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

        $this->info("Imported {$imported} dosen records from {$path}");

        return Command::SUCCESS;
    }

    private function buildProdiMap(): array
    {
        $prodiList = Prodi::all();
        $map = [];

        foreach ($prodiList as $p) {
            $map[$p->kode_prodi] = $p->id;
            $map[$p->nama_prodi] = $p->id;
        }

        $aliases = [
            'S1 Teknologi Industri' => 'S1 Teknik Industri',
        ];

        foreach ($aliases as $alias => $canonical) {
            if (isset($map[$canonical])) {
                $map[$alias] = $map[$canonical];
            }
        }

        return $map;
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

        // 3+ words: try to detect last 2 as nama_belakang
        $namaDepan = implode(' ', array_slice($parts, 0, $count - 2));
        $namaBelakang = implode(' ', array_slice($parts, -2));

        return [$namaDepan, $namaBelakang];
    }
}
