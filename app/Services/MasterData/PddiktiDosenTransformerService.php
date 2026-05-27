<?php

namespace App\Services\MasterData;

use App\Models\Prodi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PddiktiDosenTransformerService
{
    private array $prodiCache = [];

    public function transform(array $row, array $headers): ?array
    {
        $data = $this->combineHeaders($headers, $row);

        $nidn = $this->cleanNidn($data['Nomor Registrasi'] ?? $data['nomor_registrasi'] ?? '');
        $nuptk = $this->cleanValue($data['NUPTK'] ?? $data['nuptk'] ?? '');
        $nama = $this->cleanValue($data['Nama'] ?? $data['nama'] ?? '');
        $nip = $this->cleanValue($data['NIP'] ?? $data['nip'] ?? '');

        if (empty($nama)) {
            return null;
        }

        if (empty($nidn) && empty($nuptk)) {
            return null;
        }

        [$namaDepan, $namaBelakang] = $this->splitNamaPddikti($nama);
        $jabatanFungsional = $this->cleanJabatanFungsional($data['Jabatan Fungsional'] ?? $data['jabatan_fungsional'] ?? null);
        $statusSerdos = $this->cleanStatusSerdos($data['Status Serdos'] ?? $data['status_serdos'] ?? null);
        $prodiId = $this->resolveProdi($data['Penempatan'] ?? $data['penempatan'] ?? '');

        return [
            'nidn' => $nidn ?: null,
            'nip' => $nip ?: null,
            'nuptk' => $nuptk ?: null,
            'nama_depan' => $namaDepan,
            'nama_belakang' => $namaBelakang ?: null,
            'prodi_id' => $prodiId,
            'pendidikan_terakhir' => $this->cleanValue($data['Pendidikan Terakhir'] ?? $data['pendidikan_terakhir'] ?? null),
            'jabatan_fungsional' => $jabatanFungsional,
            'kepangkatan' => $this->cleanValue($data['Kepangkatan'] ?? $data['kepangkatan'] ?? null),
            'rumpun_ilmu' => $this->cleanValue($data['Rumpun Ilmu'] ?? $data['rumpun_ilmu'] ?? null),
            'status_aktivitas' => $this->mapStatusAktivitas($data['Status Aktivitas'] ?? $data['status_aktivitas'] ?? 'Aktif'),
            'status_serdos' => $statusSerdos,
            'status_pegawai' => $this->cleanValue($data['Status Pegawai'] ?? $data['status_pegawai'] ?? null),
            'ikatan_kerja' => $this->mapIkatanKerja($data['Ikatan Kerja'] ?? $data['ikatan_kerja'] ?? null),
        ];
    }

    public function splitNamaPddikti(string $fullName): array
    {
        $fullName = trim($fullName);
        $fullName = str_replace('`', "'", $fullName);

        $parts = preg_split('/\s+/', $fullName);
        $parts = array_values(array_filter($parts, fn($p) => $p !== ''));
        $count = count($parts);

        if ($count === 0) {
            return ['', ''];
        }
        if ($count === 1) {
            return [$parts[0], ''];
        }

        $namaBelakang = $parts[$count - 1];
        $namaDepan = implode(' ', array_slice($parts, 0, $count - 1));

        return [$namaDepan, $namaBelakang];
    }

    public function cleanJabatanFungsional(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);

        if (preg_match('/^(.+?)-\d/', $value, $m)) {
            return trim($m[1]);
        }

        return $value;
    }

    public function cleanStatusSerdos(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $clean = strip_tags(trim($value));
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5);

        return $clean ?: null;
    }

    public function cleanNidn(mixed $value): string
    {
        $nidn = trim((string) $value);
        $nidn = preg_replace('/[^0-9]/', '', $nidn);

        if (strlen($nidn) < 8) {
            return '';
        }

        if (strlen($nidn) < 10) {
            $nidn = str_pad($nidn, 10, '0', STR_PAD_LEFT);
        }

        return $nidn;
    }

    public function resolveProdi(string $penempatan): ?int
    {
        if (empty($penempatan)) {
            return null;
        }

        $penempatan = trim($penempatan);

        $prodiName = preg_replace('/\s*-\s*/', ' ', $penempatan);
        $prodiName = trim($prodiName);

        if (isset($this->prodiCache[$prodiName])) {
            return $this->prodiCache[$prodiName];
        }

        $prodi = Prodi::where('nama_prodi', $prodiName)->first();

        if (!$prodi) {
            $prodi = Prodi::where('nama_prodi', 'LIKE', "%{$prodiName}%")->first();
        }

        if (!$prodi) {
            $parts = explode(' ', $prodiName);
            $jenjang = $parts[0] ?? '';
            $nama = implode(' ', array_slice($parts, 1));

            if (!empty($jenjang) && !empty($nama)) {
                $prodi = Prodi::where('jenjang', $jenjang)
                    ->where('nama_prodi', 'LIKE', "%{$nama}%")
                    ->first();
            }
        }

        $id = $prodi?->id;
        $this->prodiCache[$prodiName] = $id;

        return $id;
    }

    public function mapStatusAktivitas(string $value): string
    {
        $value = trim($value);

        return match (strtolower($value)) {
            'aktif', 'active', '1', 'ya' => 'aktif',
            'tidak aktif', 'inactive', 'nonaktif', 'tidak_aktif' => 'tidak_aktif',
            'cuti' => 'cuti',
            default => 'aktif',
        };
    }

    public function mapIkatanKerja(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);

        return match ($value) {
            'Dosen Tetap' => 'tetap',
            'Dosen Tidak Tetap' => 'tidak_tetap',
            'Dosen Tetap Perjanjian Kerja Waktu Tertentu' => 'tetap_kontrak',
            default => Str::slug($value, '_'),
        };
    }

    public function getProdiCache(): array
    {
        return $this->prodiCache;
    }

    private function combineHeaders(array $headers, array $row): array
    {
        $data = [];
        $count = min(count($headers), count($row));

        for ($i = 0; $i < $count; $i++) {
            $key = trim((string) $headers[$i]);
            $data[$key] = $row[$i] ?? '';
        }

        return $data;
    }

    private function cleanValue($value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $cleaned = trim((string) $value);

        return $cleaned !== '' ? $cleaned : null;
    }
}
