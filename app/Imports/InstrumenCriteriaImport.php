<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InstrumenCriteriaImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        return $rows->map(function ($row) {
            return [
                'kode' => $row['kode'] ?? $row['code'] ?? '',
                'nama' => $row['nama_kriteria'] ?? $row['kriteria'] ?? $row['name'] ?? '',
                'bobot' => (float) ($row['bobot'] ?? $row['weight'] ?? 1),
            ];
        })->filter(fn ($item) => ! empty($item['kode']))->values();
    }
}
