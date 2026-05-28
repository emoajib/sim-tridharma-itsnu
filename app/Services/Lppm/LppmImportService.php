<?php

namespace App\Services\Lppm;

use App\Imports\LppmPenelitianImport;
use App\Imports\LppmPkmImport;
use App\Models\ImportHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class LppmImportService
{
    public function importPenelitian($file, ?int $userId = null): array
    {
        $history = ImportHistory::create([
            'type' => 'lppm_penelitian',
            'file_name' => $file instanceof \Illuminate\Http\UploadedFile
                ? $file->getClientOriginalName()
                : basename($file),
            'file_path' => $file instanceof \Illuminate\Http\UploadedFile
                ? $file->store('imports/lppm')
                : $file,
            'user_id' => $userId,
            'status' => 'processing',
        ]);

        try {
            Excel::import(new LppmPenelitianImport($history->id), $file);

            $history->refresh();
            $history->update(['status' => $history->failed_rows > 0 ? 'completed_with_errors' : 'completed']);

            return [
                'status' => 'success',
                'history_id' => $history->id,
            ];
        } catch (\Throwable $e) {
            $history->update([
                'status' => 'failed',
                'errors' => ['message' => $e->getMessage()],
            ]);

            Log::error('LPPM Import Penelitian failed: ' . $e->getMessage());

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'history_id' => $history->id,
            ];
        }
    }

    public function importPkm($file, ?int $userId = null): array
    {
        $history = ImportHistory::create([
            'type' => 'lppm_pkm',
            'file_name' => $file instanceof \Illuminate\Http\UploadedFile
                ? $file->getClientOriginalName()
                : basename($file),
            'file_path' => $file instanceof \Illuminate\Http\UploadedFile
                ? $file->store('imports/lppm')
                : $file,
            'user_id' => $userId,
            'status' => 'processing',
        ]);

        try {
            Excel::import(new LppmPkmImport($history->id), $file);

            $history->refresh();
            $history->update(['status' => $history->failed_rows > 0 ? 'completed_with_errors' : 'completed']);

            return [
                'status' => 'success',
                'history_id' => $history->id,
            ];
        } catch (\Throwable $e) {
            $history->update([
                'status' => 'failed',
                'errors' => ['message' => $e->getMessage()],
            ]);

            Log::error('LPPM Import PKM failed: ' . $e->getMessage());

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'history_id' => $history->id,
            ];
        }
    }

    public function getTemplateUrl(string $type = 'penelitian'): string
    {
        $path = "templates/template_import_lppm_{$type}.xlsx";

        if (!Storage::disk('local')->exists($path)) {
            $generator = app(LppmTemplateGenerator::class);
            $method = 'generate' . ucfirst($type) . 'Template';
            $generator->$method(Storage::disk('local')->path($path));
        }

        return Storage::disk('local')->url($path);
    }
}
