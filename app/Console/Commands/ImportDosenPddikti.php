<?php

namespace App\Console\Commands;

use App\Models\Dosen;
use App\Services\MasterData\PddiktiDosenTransformerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportDosenPddikti extends Command
{
    protected $signature = 'dosen:import-pddikti
        {file=data/Data_dosen.xlsx : Path ke file export SISTER/PDDikti}
        {--dry-run : Jalankan simulasi tanpa perubahan database}
        {--skip-user : Hanya update data dosen, jangan buat user}';

    protected $description = 'Import data dosen dari file export SISTER/PDDikti (.xlsx)';

    public function handle(): int
    {
        $path = base_path($this->argument('file'));
        $dryRun = $this->option('dry-run');
        $skipUser = $this->option('skip-user');

        if (!file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return Command::FAILURE;
        }

        $this->info("Memproses file: {$path}");
        if ($dryRun) {
            $this->warn('Mode DRY-RUN: tidak ada perubahan database');
        }

        $transformer = new PddiktiDosenTransformerService;

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (empty($rows)) {
            $this->error('File kosong');
            return Command::FAILURE;
        }

        $this->line("Baris 0 (raw): " . json_encode(array_slice($rows[0] ?? [], 0, 3)));
        $this->line("Baris 1 (raw): " . json_encode(array_slice($rows[1] ?? [], 0, 3)));

        $firstCell = is_string($rows[0][0] ?? null) ? $rows[0][0] : (string) ($rows[0][0] ?? '');
        if (str_contains($firstCell, 'SISTER')) {
            array_shift($rows);
        }

        $headers = $rows[0] ?? [];
        $dataRows = array_slice($rows, 1);

        $this->info("Headers: " . json_encode($headers));
        $this->line("Total baris data: " . count($dataRows));

        if (!$dryRun) {
            DB::beginTransaction();
        }

        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 1;

            try {
                $transformed = $transformer->transform($row, $headers);

                if ($transformed === null) {
                    $failedCount++;
                    $errors[] = "Baris {$rowNumber}: Tidak dapat mengidentifikasi dosen (NIDN/NUPTK/Nama kosong)";
                    continue;
                }

                $namaLengkap = trim(($transformed['nama_depan'] ?? '') . ' ' . ($transformed['nama_belakang'] ?? ''));
                $this->line(sprintf(
                    "  [%3d] %s (NIDN: %s, Prodi: %s)",
                    $rowNumber,
                    $namaLengkap ?: '-',
                    $transformed['nidn'] ?? '-',
                    $transformed['prodi_id'] ?? '-'
                ));

                if ($dryRun) {
                    $successCount++;
                    continue;
                }

                if ($transformed['nidn']) {
                    Dosen::updateOrCreate(
                        ['nidn' => $transformed['nidn']],
                        $transformed
                    );
                } elseif ($transformed['nuptk']) {
                    Dosen::updateOrCreate(
                        ['nuptk' => $transformed['nuptk']],
                        $transformed
                    );
                } else {
                    $failedCount++;
                    $errors[] = "Baris {$rowNumber}: Tidak memiliki NIDN maupun NUPTK.";
                    continue;
                }

                $successCount++;

            } catch (\Throwable $e) {
                $failedCount++;
                $errors[] = "Baris {$rowNumber}: {$e->getMessage()}";
            }
        }

        if (!$dryRun) {
            DB::commit();
        }

        $this->newLine();
        $this->table(
            ['Metrik', 'Nilai'],
            [
                ['Total baris', count($dataRows)],
                ['Berhasil', $successCount],
                ['Gagal', $failedCount],
                ['Mode', $dryRun ? 'DRY-RUN (simulasi)' : 'LIVE'],
            ]
        );

        if (!empty($errors)) {
            $this->newLine();
            $this->warn('Detail error:');
            foreach ($errors as $error) {
                $this->line("  - {$error}");
            }
        }

        return $failedCount === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
