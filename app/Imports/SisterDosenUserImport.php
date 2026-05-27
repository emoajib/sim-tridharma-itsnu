<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\User;
use App\Services\MasterData\PddiktiDosenTransformerService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Spatie\Permission\Models\Role;

/**
 * Dedicated Importer untuk file export SISTER (Data_dosen.xlsx / Data_dosen.csv).
 *
 * Aturan Mode Aman (sesuai keputusan proyek):
 * - Hanya meng-update field yang aman: name, is_active, dosen_id, prodi_id
 * - TIDAK PERNAH menyentuh role user (baik saat create maupun update)
 * - TIDAK PERNAH menyentuh gelar_depan & gelar_belakang (sengaja dibiarkan untuk diisi manual)
 * - Saat user baru: otomatis diberi role "Dosen" saja
 * - Saat re-upload: data role yang sudah ada tidak boleh berubah
 * - Mendukung pencocokan dosen via NIDN (prioritas) atau NUPTK
 *
 * Status implementasi:
 * - Mode Aman penuh sudah aktif (role & gelar tidak pernah disentuh)
 * - Mode Dry-Run / Simulasi sudah berfungsi (gunakan constructor dengan true)
 * - Penyimpanan snapshot data asli SISTER: fondasi sudah ada, storage penuh menunggu kolom DB
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

    /**
     * Header asli ada di baris ke-2.
     * Data pertama ada di baris ke-3 (setelah judul + header).
     */
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
        $isFirstRow = true;

        foreach ($rows as $index => $rawRow) {
            try {
                // Normalisasi key Excel menjadi snake_case lowercase
                $row = [];
                foreach ($rawRow as $key => $value) {
                    $cleanKey = Str::snake(strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', trim((string)$key))));
                    $row[$cleanKey] = $value;
                }

                // === DIAGNOSTIC LOGGING (header detection) ===
                if ($isFirstRow) {
                    Log::info('SisterDosenUserImport - Header row detected (after WithStartRow)', [
                        'row_index' => $index + 1,
                        'normalized_keys' => array_keys($row),
                        'sample_values' => array_slice($row, 0, 6, true),
                    ]);
                    $isFirstRow = false;
                }

                // Ekstraksi fleksibel untuk export SISTER
                $nomorRegistrasi = $this->cleanValue(
                    $row['nomor_registrasi'] ??
                    $row['no_reg'] ??
                    $row['nomor_registrasi'] ??
                    $row['nomor registrasi'] ??
                    $row['no'] ?? null
                );
                $nama = $this->cleanValue($row['nama'] ?? $row['nama_dosen'] ?? null);
                $nip = $this->cleanValue($row['nip'] ?? null);
                $statusAktivitas = $this->cleanValue($row['status_aktivitas'] ?? 'Aktif');
                $jabatanFungsional = $this->cleanValue($row['jabatan_fungsional'] ?? null);
                $statusSerdos = $this->cleanValue($row['status_serdos'] ?? null);
                $pendidikanTerakhir = $this->cleanValue($row['pendidikan_terakhir'] ?? null);
                $kepangkatan = $this->cleanValue($row['kepangkatan'] ?? null);
                $rumpunIlmu = $this->cleanValue($row['rumpun_ilmu'] ?? null);
                $statusPegawai = $this->cleanValue($row['status_pegawai'] ?? null);
                $ikatanKerja = $this->cleanValue($row['ikatan_kerja'] ?? null);
                $penempatan = $this->cleanValue($row['penempatan'] ?? null);

                // Skip baris header / tidak valid
                if (empty($nomorRegistrasi) || empty($nama) || !preg_match('/\d{8,}/', (string)$nomorRegistrasi)) {
                    $this->skippedCount++;

                    // Logging yang lebih baik untuk debugging
                    if ($this->skippedCount <= 3) {   // hanya log 3 baris pertama yang di-skip
                        Log::warning('SisterDosenUserImport - Row skipped', [
                            'row' => $index + 1,
                            'nomor_registrasi_raw' => $nomorRegistrasi,
                            'nama_raw' => $nama,
                            'has_digit_8plus' => preg_match('/\d{8,}/', (string)($nomorRegistrasi ?? '')),
                            'available_keys' => array_keys($row),
                        ]);
                    }
                    continue;
                }

                // 1. Normalisasi NIDN (perbaikan utama untuk leading zero)
                $normalizedNidn = $this->normalizeNidn($nomorRegistrasi);

                Log::debug("SisterDosenUserImport - Mencari Dosen", [
                    'row' => $index + 1,
                    'nama' => $nama,
                    'raw_nomor_registrasi' => $nomorRegistrasi,
                    'normalized_nidn' => $normalizedNidn,
                    'nip' => $nip,
                ]);

                // 2. Generate email unik
                $email = $this->generateUniqueEmail($nama, $normalizedNidn);

                // 3. Base attributes (Mode Aman: hanya field aman)
                $attributes = [
                    'name' => $nama,
                    'is_active' => $this->isActive($statusAktivitas),
                ];

                if (!User::where('email', $email)->exists()) {
                    $attributes['password'] = Hash::make('password123');
                }

                // =============================================
                // UNIFIED DOSEN MATCHING (Mode Aman)
                // Prioritas: NIDN → NIP → NUPTK
                // =============================================
                $nuptk = $this->cleanValue($row['nuptk'] ?? null);

                $dosen = Dosen::where('nidn', $normalizedNidn)->first();
                if (!$dosen && !empty($nip)) {
                    $dosen = Dosen::where('nip', $nip)->first();
                }

                // Guard: hanya query nuptk jika kolom benar-benar ada di m_dosen
                if (!$dosen && !empty($nuptk) && Schema::hasColumn('m_dosen', 'nuptk')) {
                    $dosen = Dosen::where('nuptk', $nuptk)->first();
                }

                $wouldCreate = !User::where('email', $email)->exists();

                if ($this->dryRun) {
                    // === MODE DRY-RUN / SIMULASI ===
                    $this->dryRunResults[] = [
                        'row' => $index + 1,
                        'nama' => $nama,
                        'email' => $email,
                        'action' => $wouldCreate ? 'CREATE' : 'UPDATE',
                        'normalized_nidn' => $normalizedNidn,
                        'nuptk' => $nuptk,
                        'jabatan_fungsional' => $jabatanFungsional,
                        'status_serdos' => $statusSerdos,
                        'pendidikan_terakhir' => $pendidikanTerakhir,
                        'kepangkatan' => $kepangkatan,
                        'rumpun_ilmu' => $rumpunIlmu,
                        'status_pegawai' => $statusPegawai,
                        'ikatan_kerja' => $ikatanKerja,
                        'penempatan' => $penempatan,
                        'would_assign_dosen_role' => $wouldCreate,
                        'would_link_dosen' => $dosen?->nama,
                        'would_update_prodi' => $dosen?->prodi_id,
                        'note' => 'Role dan gelar TIDAK akan diubah (Mode Aman)',
                    ];
                    continue;
                }

                // === MODE NYATA (LIVE) - Mode Aman enforced ===
                $userAttributes = $attributes;

                if ($dosen) {
                    $userAttributes['dosen_id'] = $dosen->id;
                    $userAttributes['prodi_id'] = $dosen->prodi_id;
                }

                $user = User::updateOrCreate(['email' => $email], $userAttributes);

                // Beri role "Dosen" HANYA saat create pertama kali
                // TIDAK PERNAH menyentuh role pada re-upload / update
                if ($wouldCreate) {
                    try {
                        $user->assignRole('Dosen');
                    } catch (\Throwable $roleEx) {
                        Log::warning('SisterDosenUserImport: Role "Dosen" tidak tersedia untuk user baru', [
                            'email' => $email,
                            'error' => $roleEx->getMessage(),
                        ]);
                    }
                }

                $this->successCount++;

            } catch (\Throwable $e) {
                $this->errors[] = ['row' => $index + 1, 'error' => $e->getMessage()];
                Log::error("SisterDosenUserImport error baris " . ($index + 1), ['error' => $e->getMessage()]);
            }
        }

        Log::info('SisterDosenUserImport selesai', [
            'success' => $this->successCount,
            'skipped' => $this->skippedCount,
            'errors' => count($this->errors),
            'mode' => $this->dryRun ? 'DRY_RUN' : 'LIVE',
        ]);

        // Log ringkasan yang lebih jelas untuk debugging import
        if ($this->skippedCount > 0 && $this->successCount === 0) {
            Log::error('SisterDosenUserImport - SEMUA BARIS DI-SKIP. Periksa struktur file Excel dan WithStartRow.', [
                'total_skipped' => $this->skippedCount,
            ]);
        }
    }

    /**
     * Normalisasi NIDN menjadi 10 digit dengan leading zero.
     * Menangani kasus Excel menghapus leading zero.
     */
    protected function normalizeNidn(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $clean = preg_replace('/[^0-9]/', '', (string) $value);

        // Target 10 digit (format NIDN umum di Indonesia)
        if (strlen($clean) < 10) {
            $clean = str_pad($clean, 10, '0', STR_PAD_LEFT);
        }

        return $clean;
    }

    /**
     * Generate email unik berdasarkan nama + NIDN.
     * Domain: @itsnupekalongan.ac.id (sesuai requirement proyek)
     */
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

    /**
     * Mengembalikan hasil simulasi (hanya terisi jika $dryRun = true).
     */
    public function getDryRunResults(): array
    {
        return $this->dryRunResults;
    }
}
