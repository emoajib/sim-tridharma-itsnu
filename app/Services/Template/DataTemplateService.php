<?php
namespace App\Services\Template;

use Illuminate\Support\Facades\Storage;

class DataTemplateService
{
    public function download(string $type): string
    {
        $headers = match ($type) {
            'users' => ['Nama', 'Email', 'Role', 'Prodi', 'Password'],
            'dosen' => ['NIDN', 'Nama Depan', 'Nama Belakang', 'Prodi', 'Status'],
            default => abort(404, 'Template type not found'),
        };

        $filename = "template_{$type}.csv";
        $path = storage_path("app/templates/{$filename}");

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $handle = fopen($path, 'w');
        fputcsv($handle, $headers);
        fclose($handle);

        return $path;
    }
}
