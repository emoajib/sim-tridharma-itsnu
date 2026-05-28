<?php

namespace App\Services\Sinta;

use App\Imports\SintaPenelitianImport;
use App\Imports\SintaPkmImport;
use App\Imports\SintaPublikasiImport;
use App\Models\Dosen;
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
        $before = Penelitian::count();
        $this->universalImport(new SintaPenelitianImport, $file);
        $imported = Penelitian::count() - $before;

        return ['imported' => $imported, 'type' => 'penelitian'];
    }

    public function importPublikasi($file): array
    {
        $before = Publikasi::count();
        $this->universalImport(new SintaPublikasiImport, $file);
        $imported = Publikasi::count() - $before;

        return ['imported' => $imported, 'type' => 'publikasi'];
    }

    public function importPkm($file): array
    {
        $before = Pkm::count();
        $this->universalImport(new SintaPkmImport, $file);
        $imported = Pkm::count() - $before;

        return ['imported' => $imported, 'type' => 'pkm'];
    }

    public function importDosen($file): array
    {
        $before = Dosen::count();
        $this->universalImport(new \App\Imports\SintaDosenImport, $file);
        $imported = Dosen::count() - $before;

        return ['imported' => $imported, 'type' => 'dosen'];
    }
}
