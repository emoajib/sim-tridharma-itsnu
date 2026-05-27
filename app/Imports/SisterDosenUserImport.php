<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\User;
use App\Services\MasterData\PddiktiDosenTransformerService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Spatie\Permission\Models\Role;

/**
 * Dedicated Importer untuk file export SISTER (Data_dosen.xlsx / Data_dosen.csv).
 *
 * Aturan Mode Aman:
 * - TIDAK PERNAH menyentuh role user (baik saat create maupun update)
 * - TIDAK PERNAH menyentuh gelar_depan & gelar_belakang (sengaja dibiarkan untuk diisi manual)
 * - Saat user baru: otomatis diberi role "Dosen" saja
 * - Saat re-upload: data role yang sudah ada tidak boleh berubah
 * - Mendukung pencocokan dosen via NIDN (prioritas) → NIP → NUPTK
 */
class SisterDosenUserImport implements ToCollection, WithHeadingRow, WithStartRow
{
    protected int $successCount = 0;
    protected int $skippedCount = 0;
    protected array $errors = [];

    protected bool $dryRun = false;
    protected array $dryRunResults = [];
    protected PddiktiDosenTransformerService $transformer;

    public function __construct(bool $dryRun = false)
    {
        $this->dryRun = $dryRun;
        $this->transformer = new PddiktiDosenTransformerService;
    }

    public function headingRow(): int
    {
        return 2;
    }

    public function startRow(): int
    {
        return 3;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            $isFirstRow = true;

            foreach ($rows as $index => $rawRow) {
                try {
                    $row = $this->normalizeKeys($rawRow);

                    if ($isFirstRow) {
                        Log::info('SisterDosenUserImport - Header row detected', [
                            'row_index' => $index + 1,
                            'normalized_keys' => array_keys($row),
                        ]);
                        $isFirstRow = false;
                    }

                    $nuptk = $this->cleanValue($row['nuptk'] ?? null);
                    $nomorRegistrasi = $this->cleanValue(
                        $row['nomor_registrasi'] ?? $row['no_reg'] ?? $row['no'] ?? null
                    );
                    $nama = $this->cleanValue($row['nama'] ?? $row['nama_dosen'] ?? null);

                    if (empty($nama)) {
                        $this->skippedCount++;
                        continue;
                    }

                    $hasNidn = !empty($nomorRegistrasi) && preg_match('/\d{8,}/', $nomorRegistrasi);
                    $hasNuptk = !empty($nuptk) && preg_match('/\d{8,}/', $nuptk);

                    if (!$hasNidn && !$hasNuptk) {
                        $this->skippedCount++;
                        if ($this->skippedCount <= 3) {
                            Log::warning('SisterDosenUserImport - Row skipped (no identifier)', [
                                'row' => $index + 1,
                                'nama' => $nama,
                                'nomor_registrasi' => $nomorRegistrasi,
                                'nuptk' => $nuptk,
                            ]);
                        }
                        continue;
                    }

                    $headers = array_keys($row);
                    $transformed = $this->transformer->transform(array_values($row), $headers);

                    if ($transformed === null) {
                        $this->skippedCount++;
                        continue;
                    }

                    $normalizedNidn = $transformed['nidn'] ?? null;
                    $email = $this->generateUniqueEmail($nama, $normalizedNidn);

                    $attributes = [
                        'name' => $nama,
                        'is_active' => $this->isActive($row['status_aktivitas'] ?? 'Aktif'),
                    ];

                    if (!User::where('email', $email)->exists()) {
                        $attributes['password'] = Hash::make('password123');
                    }

                    $dosen = Dosen::where('nidn', $normalizedNidn)->first();
                    if (!$dosen && !empty($transformed['nip'])) {
                        $dosen = Dosen::where('nip', $transformed['nip'])->first();
                    }
                    if (!$dosen && !empty($transformed['nuptk'])) {
                        $dosen = Dosen::where('nuptk', $transformed['nuptk'])->first();
                    }

                    $wouldCreateUser = !User::where('email', $email)->exists();
                    $wouldCreateDosen = $dosen === null;

                    if ($this->dryRun) {
                        $this->dryRunResults[] = [
                            'row' => $index + 1,
                            'nama' => $nama,
                            'nama_depan' => $transformed['nama_depan'],
                            'nama_belakang' => $transformed['nama_belakang'],
                            'email' => $email,
                            'action' => $wouldCreateUser ? 'CREATE' : 'UPDATE',
                            'dosen_action' => $wouldCreateDosen ? 'CREATE' : 'UPDATE',
                            'normalized_nidn' => $normalizedNidn,
                            'nuptk' => $transformed['nuptk'],
                            'jabatan_fungsional' => $transformed['jabatan_fungsional'],
                            'status_serdos' => $transformed['status_serdos'],
                            'pendidikan_terakhir' => $transformed['pendidikan_terakhir'],
                            'kepangkatan' => $transformed['kepangkatan'],
                            'rumpun_ilmu' => $transformed['rumpun_ilmu'],
                            'status_pegawai' => $transformed['status_pegawai'],
                            'ikatan_kerja' => $transformed['ikatan_kerja'],
                            'penempatan' => $this->cleanValue($row['penempatan'] ?? null),
                            'prodi_id' => $transformed['prodi_id'],
                            'would_assign_dosen_role' => $wouldCreateUser,
                            'would_link_dosen' => $dosen?->nama_depan . ' ' . $dosen?->nama_belakang,
                            'note' => 'Role dan gelar TIDAK akan diubah (Mode Aman)',
                        ];
                        continue;
                    }

                    if ($wouldCreateDosen) {
                        $dosen = Dosen::create($transformed);
                    } else {
                        $dosen->update($transformed);
                    }

                    $userAttributes = $attributes;
                    $userAttributes['dosen_id'] = $dosen->id;
                    $userAttributes['prodi_id'] = $dosen->prodi_id;

                    $user = User::updateOrCreate(['email' => $email], $userAttributes);

                    if ($wouldCreateUser) {
                        try {
                            $user->assignRole('Dosen');
                        } catch (\Throwable $roleEx) {
                            Log::warning('SisterDosenUserImport: Role "Dosen" tidak tersedia', [
                                'email' => $email,
                                'error' => $roleEx->getMessage(),
                            ]);
                        }
                    }

                    $this->successCount++;

                } catch (\Throwable $e) {
                    $this->errors[] = ['row' => $index + 1, 'error' => $e->getMessage()];
                    Log::error("SisterDosenUserImport error baris " . ($index + 1), [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    if ($this->dryRun) {
                        throw $e;
                    }
                }
            }

            if (!$this->dryRun) {
                DB::commit();
            } else {
                DB::rollBack();
            }

            Log::info('SisterDosenUserImport selesai', [
                'success' => $this->successCount,
                'skipped' => $this->skippedCount,
                'errors' => count($this->errors),
                'mode' => $this->dryRun ? 'DRY_RUN' : 'LIVE',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('SisterDosenUserImport - Transaction rolled back', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function normalizeKeys(Collection $rawRow): array
    {
        $row = [];
        foreach ($rawRow as $key => $value) {
            $cleanKey = Str::snake(strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', trim((string) $key))));
            $row[$cleanKey] = $value;
        }
        return $row;
    }

    protected function generateUniqueEmail(string $nama, ?string $nidn): string
    {
        $slug = Str::slug($nama, '.');
        $baseEmail = $slug . '.' . ($nidn ?? Str::random(6)) . '@itsnupekalongan.ac.id';

        $email = $baseEmail;
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = $slug . '.' . ($nidn ?? Str::random(6)) . '.' . $counter . '@itsnupekalongan.ac.id';
            $counter++;
        }

        return $email;
    }

    protected function isActive(?string $status): bool
    {
        $s = strtolower(trim((string) $status));
        return in_array($s, ['aktif', 'active', '1', 'ya']);
    }

    protected function cleanValue($value): ?string
    {
        if (is_null($value)) {
            return null;
        }
        return trim((string) $value);
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getErrorCount(): int
    {
        return count($this->errors);
    }

    public function getDryRunResults(): array
    {
        return $this->dryRunResults;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }
}
