<?php

namespace App\Services\Sinta;

use App\Imports\SintaDosenImport;
use App\Imports\SintaPenelitianImport;
use App\Imports\SintaPkmImport;
use App\Imports\SintaPublikasiImport;
use App\Models\Penelitian;
use App\Models\Pkm;
use App\Models\Publikasi;
use Maatwebsite\Excel\Facades\Excel;

class SintaImportService
{
    protected array $readers = [
        null,
        \Maatwebsite\Excel\Excel::XLSX,
        \Maatwebsite\Excel\Excel::XLS,
        \Maatwebsite\Excel\Excel::HTML,
        \Maatwebsite\Excel\Excel::CSV,
        \Maatwebsite\Excel\Excel::TSV,
    ];

    public function universalImport($importClass, $file): void
    {
        $lastException = null;

        foreach ($this->readers as $reader) {
            try {
                Excel::import($importClass, $file, null, $reader);

                return;
            } catch (\Exception $e) {
                $lastException = $e;
            }
        }

        throw $lastException;
    }

    public function importPenelitian($file): array
    {
        return $this->importDual($file, 'penelitian', SintaPenelitianImport::class);
    }

    public function importPublikasi($file): array
    {
        return $this->importDual($file, 'publikasi', SintaPublikasiImport::class);
    }

    public function importPkm($file): array
    {
        return $this->importDual($file, 'pkm', SintaPkmImport::class);
    }

    protected function importDual($file, string $type, string $importClass): array
    {
        $this->universalImport(new SintaDosenImport, $file);

        $modelClass = match ($type) {
            'penelitian' => Penelitian::class,
            'publikasi' => Publikasi::class,
            'pkm' => Pkm::class,
            default => throw new \InvalidArgumentException("Unknown import type: {$type}"),
        };

        $before = $modelClass::count();
        $this->universalImport(new $importClass, $file);
        $imported = $modelClass::count() - $before;

        return [
            'imported' => $imported,
            'type' => $type,
        ];
    }
}
