<?php

namespace App\Services\Admin;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function upload(UploadedFile $file, string $settingKey, string $folder, string $disk = 'public'): string
    {
        $currentPath = Setting::get($settingKey);
        if ($currentPath) {
            Storage::disk($disk)->delete($currentPath);
        }

        $path = $file->store($folder, $disk);
        Setting::set($settingKey, $path, 'string', "Path file {$settingKey}");

        return $path;
    }

    public function remove(string $settingKey, string $disk = 'public'): void
    {
        $currentPath = Setting::get($settingKey);
        if ($currentPath) {
            Storage::disk($disk)->delete($currentPath);
            Setting::set($settingKey, null, 'string');
        }
    }
}
