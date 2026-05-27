<?php
namespace App\Services\MasterData;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Facades\Excel;

class MasterDataTemplateService
{
    private array $lookupCache = [];

    private function getPddiktiColumns(): array
    {
        return [
            'No', 'Nomor Registrasi', 'NUPTK', 'Nama', 'NIP',
            'Jabatan Fungsional', 'Kepangkatan', 'Pendidikan Terakhir',
            'Rumpun Ilmu', 'Status Serdos', 'Status Pegawai',
            'Ikatan Kerja', 'Status Aktivitas', 'Penempatan',
        ];
    }

    public function getTypes(): array
    {
        return [
            'dosen' => [
                'label' => 'Dosen',
                'table' => 'm_dosen',
                'columns' => ['NIDN', 'Nama Depan', 'Nama Belakang', 'Prodi', 'Status', 'Email', 'No HP'],
                'field_map' => [
                    'NIDN' => 'nidn',
                    'Nama Depan' => 'nama_depan',
                    'Nama Belakang' => 'nama_belakang',
                    'Status' => 'status_aktivitas',
                    'Email' => 'email',
                    'No HP' => 'telepon',
                ],
                'lookups' => [
                    'Prodi' => ['table' => 'm_prodi', 'from' => 'nama_prodi', 'to' => 'id', 'field' => 'prodi_id'],
                ],
                'required' => ['NIDN', 'Nama Depan', 'Prodi'],
                'defaults' => ['status_aktivitas' => 'aktif'],
            ],
            'mahasiswa' => [
                'label' => 'Mahasiswa',
                'table' => 'm_mahasiswa',
                'columns' => ['NIM', 'Nama', 'Prodi', 'Angkatan', 'Email', 'No HP'],
                'field_map' => [
                    'NIM' => 'nim',
                    'Nama' => 'nama',
                    'Angkatan' => 'angkatan',
                    'Email' => 'email',
                    'No HP' => 'telepon',
                ],
                'lookups' => [
                    'Prodi' => ['table' => 'm_prodi', 'from' => 'nama_prodi', 'to' => 'id', 'field' => 'prodi_id'],
                ],
                'required' => ['NIM', 'Nama', 'Prodi', 'Angkatan'],
                'defaults' => [],
            ],
            'mata_kuliah' => [
                'label' => 'Mata Kuliah',
                'table' => 'm_mata_kuliah',
                'columns' => ['Kode MK', 'Nama MK', 'SKS', 'Semester', 'Prodi'],
                'field_map' => [
                    'Kode MK' => 'kode_mk',
                    'Nama MK' => 'nama_mk',
                    'SKS' => 'sks',
                    'Semester' => 'semester',
                ],
                'lookups' => [
                    'Prodi' => ['table' => 'm_prodi', 'from' => 'nama_prodi', 'to' => 'id', 'field' => 'prodi_id'],
                ],
                'required' => ['Kode MK', 'Nama MK', 'Prodi'],
                'defaults' => ['sks' => 2],
            ],
            'prodi' => [
                'label' => 'Program Studi',
                'table' => 'm_prodi',
                'columns' => ['Kode Prodi', 'Nama Prodi', 'Fakultas', 'Jenjang', 'Akreditasi'],
                'field_map' => [
                    'Kode Prodi' => 'kode_prodi',
                    'Nama Prodi' => 'nama_prodi',
                    'Jenjang' => 'jenjang',
                    'Akreditasi' => 'akreditasi',
                ],
                'lookups' => [
                    'Fakultas' => ['table' => 'm_fakultas', 'from' => 'nama_fakultas', 'to' => 'id', 'field' => 'fakultas_id'],
                ],
                'required' => ['Kode Prodi', 'Nama Prodi', 'Fakultas', 'Jenjang'],
                'defaults' => [],
            ],
            'kurikulum' => [
                'label' => 'Kurikulum',
                'table' => 'm_kurikulum',
                'columns' => ['Nama Kurikulum', 'Prodi', 'Tahun', 'Jumlah SKS'],
                'field_map' => [
                    'Nama Kurikulum' => 'nama_kurikulum',
                    'Tahun' => 'tahun_berlaku',
                ],
                'lookups' => [
                    'Prodi' => ['table' => 'm_prodi', 'from' => 'nama_prodi', 'to' => 'id', 'field' => 'prodi_id'],
                ],
                'required' => ['Nama Kurikulum', 'Prodi', 'Tahun'],
                'defaults' => [],
            ],
            'mitra' => [
                'label' => 'Mitra',
                'table' => 'm_mitra',
                'columns' => ['Nama Mitra', 'Alamat', 'Telepon', 'Email', 'Bidang Kerja Sama'],
                'field_map' => [
                    'Nama Mitra' => 'nama_mitra',
                    'Alamat' => 'alamat',
                    'Telepon' => 'telepon',
                    'Email' => 'email',
                    'Bidang Kerja Sama' => 'jenis_mitra',
                ],
                'lookups' => [],
                'required' => ['Nama Mitra'],
                'defaults' => ['jenis_mitra' => 'Lainnya'],
            ],
            'sarana' => [
                'label' => 'Sarana',
                'table' => 'm_sarana',
                'columns' => ['Nama Sarana', 'Prodi', 'Jumlah', 'Kondisi', 'Lokasi'],
                'field_map' => [
                    'Nama Sarana' => 'nama_sarana',
                    'Jumlah' => 'jumlah',
                    'Kondisi' => 'kondisi',
                ],
                'lookups' => [
                    'Prodi' => ['table' => 'm_prodi', 'from' => 'nama_prodi', 'to' => 'id', 'field' => 'prodi_id'],
                ],
                'required' => ['Nama Sarana', 'Prodi'],
                'defaults' => ['jenis_sarana' => '-', 'jumlah' => 1, 'kondisi' => 'Baik'],
            ],
            'users' => [
                'label' => 'User',
                'table' => 'users',
                'columns' => ['Nama', 'Email', 'Role', 'Prodi', 'Password'],
                'field_map' => [
                    'Nama' => 'name',
                    'Email' => 'email',
                ],
                'lookups' => [
                    'Prodi' => ['table' => 'm_prodi', 'from' => 'nama_prodi', 'to' => 'id', 'field' => 'prodi_id'],
                ],
                'required' => ['Nama', 'Email'],
                'defaults' => [],
                'has_password' => true,
            ],
            'dosen_pddikti' => [
                'label' => 'Dosen (PDDikti/SISTER)',
                'table' => 'm_dosen',
                'columns' => $this->getPddiktiColumns(),
                'field_map' => [
                    'Nomor Registrasi' => 'nidn',
                    'NIP' => 'nip',
                    'Pendidikan Terakhir' => 'pendidikan_terakhir',
                    'Status Aktivitas' => 'status_aktivitas',
                ],
                'lookups' => [],
                'required' => ['Nama'],
                'defaults' => ['status_aktivitas' => 'aktif'],
            ],
        ];
    }

    public function getTypeConfig(string $type): array
    {
        $types = $this->getTypes();
        if (!isset($types[$type])) {
            abort(404, "Tipe template '{$type}' tidak ditemukan.");
        }
        return $types[$type];
    }

    public function downloadTemplate(string $type): string
    {
        $config = $this->getTypeConfig($type);

        $filename = "template_{$type}_" . date('Ymd_His') . '.xlsx';
        $relativePath = "templates/{$filename}";

        $disk = Storage::disk('local');
        if (!$disk->exists('templates')) {
            $disk->makeDirectory('templates');
        }

        Excel::store(
            new class($config['columns']) implements FromArray, WithHeadings
            {
                private array $headers;

                public function __construct(array $headers)
                {
                    $this->headers = $headers;
                }

                public function headings(): array
                {
                    return $this->headers;
                }

                public function array(): array
                {
                    return [];
                }
            },
            $relativePath,
            'local',
            \Maatwebsite\Excel\Excel::XLSX
        );

        return $disk->path($relativePath);
    }

    public function preview(string $type, UploadedFile $file): array
    {
        $config = $this->getTypeConfig($type);

        $rows = $this->readRows($file);
        if (empty($rows)) {
            return [];
        }

        $headers = $rows[0];
        $dataRows = array_slice($rows, 1);

        $result = [];
        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 2;
            $rowData = $this->combineHeaders($headers, $row);
            $validation = $this->validateRow($config, $rowData, $rowNumber);

            $mapped = [];
            if ($validation['valid']) {
                $mapped = $this->mapRowToDb($config, $rowData);
            }

            $result[] = [
                'row_number' => $rowNumber,
                'data' => $rowData,
                'mapped' => $mapped,
                'valid' => $validation['valid'],
                'errors' => $validation['errors'],
            ];
        }

        return $result;
    }

    public function importPddikti(UploadedFile $file): ImportResult
    {
        $transformer = new PddiktiDosenTransformerService;

        $rows = $this->readRows($file);
        if (empty($rows)) {
            return new ImportResult(0, 0, 0, [['row' => 0, 'errors' => ['File kosong atau tidak dapat dibaca.']]]);
        }

        $firstCell = is_string($rows[0][0] ?? null) ? $rows[0][0] : (string) ($rows[0][0] ?? '');

        if (str_contains($firstCell, 'SISTER')) {
            array_shift($rows);
        }

        if (empty($rows)) {
            return new ImportResult(0, 0, 0, [['row' => 0, 'errors' => ['File kosong.']]]);
        }

        $headers = $rows[0];
        $dataRows = array_slice($rows, 1);

        $errors = [];
        $successCount = 0;

        DB::beginTransaction();

        try {
            foreach ($dataRows as $index => $row) {
                $rowNumber = $index + 2;

                $transformed = $transformer->transform($row, $headers);

                if ($transformed === null) {
                    $nama = $row[array_search('Nama', $headers) ?: 3] ?? '-';
                    $errors[] = [
                        'row' => $rowNumber,
                        'errors' => ['Tidak dapat mengidentifikasi dosen (NIDN/NUPTK/Nama kosong).'],
                        'data' => ['Nama' => $nama],
                    ];
                    continue;
                }

                try {
                    if ($transformed['nidn']) {
                        DB::table('m_dosen')->updateOrInsert(
                            ['nidn' => $transformed['nidn']],
                            array_merge($transformed, [
                                'is_active' => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ])
                        );
                    } elseif ($transformed['nuptk']) {
                        DB::table('m_dosen')->updateOrInsert(
                            ['nuptk' => $transformed['nuptk']],
                            array_merge($transformed, [
                                'is_active' => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ])
                        );
                    } else {
                        $errors[] = [
                            'row' => $rowNumber,
                            'errors' => ['Tidak memiliki NIDN maupun NUPTK.'],
                            'data' => $transformed,
                        ];
                        continue;
                    }
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'errors' => [$e->getMessage()],
                        'data' => $transformed,
                    ];
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return new ImportResult(count($dataRows), 0, count($dataRows), [
                ['row' => 0, 'errors' => ['Transaction rolled back: ' . $e->getMessage()]],
            ]);
        }

        return new ImportResult(
            totalRows: count($dataRows),
            successRows: $successCount,
            failedRows: count($dataRows) - $successCount,
            errors: $errors,
        );
    }

    public function previewPddikti(UploadedFile $file): array
    {
        $transformer = new PddiktiDosenTransformerService;

        $rows = $this->readRows($file);
        if (empty($rows)) {
            return [];
        }

        $firstCell = is_string($rows[0][0] ?? null) ? $rows[0][0] : (string) ($rows[0][0] ?? '');
        if (str_contains($firstCell, 'SISTER')) {
            array_shift($rows);
        }

        $headers = $rows[0];
        $dataRows = array_slice($rows, 1);

        $result = [];
        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 2;
            $transformed = $transformer->transform($row, $headers);

            $result[] = [
                'row_number' => $rowNumber,
                'data' => $row,
                'mapped' => $transformed,
                'valid' => $transformed !== null,
                'errors' => $transformed === null ? ['Tidak dapat mengidentifikasi dosen (NIDN/NUPTK/Nama kosong).'] : [],
            ];
        }

        return $result;
    }

    public function import(string $type, UploadedFile $file, ?int $userId = null): ImportResult
    {
        $config = $this->getTypeConfig($type);

        $rows = $this->readRows($file);
        if (empty($rows)) {
            return new ImportResult(0, 0, 0, [['row' => 0, 'errors' => ['File kosong atau tidak dapat dibaca.']]]);
        }

        $headers = $rows[0];
        $dataRows = array_slice($rows, 1);

        $validRows = [];
        $errors = [];

        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 2;
            $rowData = $this->combineHeaders($headers, $row);
            $validation = $this->validateRow($config, $rowData, $rowNumber);

            if ($validation['valid']) {
                $validRows[] = $this->mapRowToDb($config, $rowData);
            } else {
                $errors[] = [
                    'row' => $rowNumber,
                    'errors' => $validation['errors'],
                    'data' => $rowData,
                ];
            }
        }

        $successCount = 0;

        if (!empty($validRows)) {
            DB::transaction(function () use ($config, $validRows, &$successCount) {
                foreach (array_chunk($validRows, 100) as $chunk) {
                    $successCount += DB::table($config['table'])->insertOrIgnore($chunk);
                }
            });
        }

        return new ImportResult(
            totalRows: count($dataRows),
            successRows: $successCount,
            failedRows: count($dataRows) - $successCount,
            errors: $errors,
        );
    }

    private function readRows(UploadedFile $file): array
    {
        $import = new class implements ToCollection, WithStartRow
        {
            public ?Collection $data = null;
            public int $startRow = 1;

            public function startRow(): int
            {
                return $this->startRow;
            }

            public function collection(Collection $rows)
            {
                $this->data = $rows;
            }
        };

        Excel::import($import, $file);

        return $import->data?->toArray() ?? [];
    }

    private function combineHeaders(array $headers, array $row): array
    {
        $data = [];
        $count = min(count($headers), count($row));
        for ($i = 0; $i < $count; $i++) {
            $data[$headers[$i]] = $row[$i];
        }
        return $data;
    }

    private function validateRow(array $config, array $rowData, int $rowNumber): array
    {
        $errors = [];

        foreach ($config['required'] as $field) {
            $value = $rowData[$field] ?? '';
            if (is_null($value) || trim((string) $value) === '') {
                $errors[] = "{$field} wajib diisi.";
            }
        }

        if (isset($config['field_map']['Email']) && !empty($rowData['Email'])) {
            if (!filter_var($rowData['Email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format Email tidak valid.";
            }
        }

        if (isset($config['field_map']['SKS']) && isset($rowData['SKS']) && $rowData['SKS'] !== '' && $rowData['SKS'] !== null) {
            if (!is_numeric($rowData['SKS']) || (int) $rowData['SKS'] < 0) {
                $errors[] = "SKS harus berupa angka positif.";
            }
        }

        if (isset($config['field_map']['Jumlah']) && isset($rowData['Jumlah']) && $rowData['Jumlah'] !== '' && $rowData['Jumlah'] !== null) {
            if (!is_numeric($rowData['Jumlah']) || (int) $rowData['Jumlah'] < 0) {
                $errors[] = "Jumlah harus berupa angka positif.";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    private function mapRowToDb(array $config, array $rowData): array
    {
        $dbRow = [];

        foreach ($config['field_map'] as $excelColumn => $dbColumn) {
            if (isset($rowData[$excelColumn]) && $rowData[$excelColumn] !== '' && $rowData[$excelColumn] !== null) {
                $dbRow[$dbColumn] = $rowData[$excelColumn];
            }
        }

        foreach ($config['lookups'] as $excelColumn => $lookup) {
            if (isset($rowData[$excelColumn]) && $rowData[$excelColumn] !== '' && $rowData[$excelColumn] !== null) {
                $resolvedId = $this->resolveLookup($lookup, $rowData[$excelColumn]);
                if ($resolvedId !== null) {
                    $dbRow[$lookup['field']] = $resolvedId;
                }
            }
        }

        foreach ($config['defaults'] as $dbColumn => $defaultValue) {
            if (!isset($dbRow[$dbColumn]) || $dbRow[$dbColumn] === '' || $dbRow[$dbColumn] === null) {
                $dbRow[$dbColumn] = $defaultValue;
            }
        }

        $dbRow['is_active'] = true;
        $dbRow['created_at'] = now();
        $dbRow['updated_at'] = now();

        if (isset($config['has_password']) && $config['has_password']) {
            if (isset($rowData['Password']) && !empty($rowData['Password'])) {
                $dbRow['password'] = Hash::make($rowData['Password']);
            } else {
                $dbRow['password'] = Hash::make('password');
            }
        }

        if ($config['table'] === 'users' && isset($rowData['Role']) && !empty($rowData['Role'])) {
            $dbRow['role_name'] = $rowData['Role'];
        }

        return $dbRow;
    }

    private function resolveLookup(array $lookup, mixed $value): ?int
    {
        $cacheKey = $lookup['table'] . '.' . $lookup['from'] . '.' . $value;
        if (isset($this->lookupCache[$cacheKey])) {
            return $this->lookupCache[$cacheKey];
        }

        $result = DB::table($lookup['table'])
            ->where($lookup['from'], $value)
            ->whereNull('deleted_at')
            ->value($lookup['to']);

        $this->lookupCache[$cacheKey] = $result;

        return $result;
    }
}
