<?php

namespace App\Http\Controllers\Api;

use App\Events\ImportCompleted;
use App\Http\Controllers\Controller;
use App\Models\ImportHistory;
use App\Services\MasterData\MasterDataTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DataImportController extends Controller
{
    public function __construct(
        protected MasterDataTemplateService $templateService
    ) {}

    public function templates()
    {
        $types = $this->templateService->getTypes();

        $templates = collect($types)->map(function ($config, $type) {
            return [
                'type' => $type,
                'label' => 'Template Data ' . $config['label'],
                'fields' => $config['columns'],
            ];
        })->values()->toArray();

        return inertia('DataImport/Index', ['templates' => $templates]);
    }

    public function downloadTemplate(string $type)
    {
        $config = $this->templateService->getTypeConfig($type);
        $path = $this->templateService->downloadTemplate($type);

        $filename = "template_{$type}.xlsx";

        return response()->download($path, $filename)->deleteFileAfterSend();
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:dosen,mahasiswa,mata_kuliah,prodi,kurikulum,mitra,sarana,users',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $type = $validated['type'];
        $file = $request->file('file');

        $result = $this->templateService->import(
            $type,
            $file,
            $request->user()?->id
        );

        ImportHistory::create([
            'type' => $type,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $file->store('imports/' . $type),
            'total_rows' => $result->totalRows,
            'success_rows' => $result->successRows,
            'failed_rows' => $result->failedRows,
            'errors' => $result->errors,
            'user_id' => $request->user()?->id,
            'status' => $result->failedRows > 0 ? 'completed' : 'completed',
        ]);

        event(new ImportCompleted(
            userId: $request->user()?->id ?? 0,
            type: $type,
            successRows: $result->successRows,
            failedRows: $result->failedRows,
        ));

        if ($result->failedRows > 0) {
            $message = "Import selesai. {$result->successRows} baris berhasil, {$result->failedRows} baris gagal.";
            return redirect()->route('data-import.history')
                ->with('warning', $message)
                ->with('import_errors', $result->errors);
        }

        return redirect()->route('data-import.history')
            ->with('success', "Import berhasil! {$result->successRows} baris data {$type} telah ditambahkan.");
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:dosen,mahasiswa,mata_kuliah,prodi,kurikulum,mitra,sarana,users',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $previewData = $this->templateService->preview(
            $validated['type'],
            $request->file('file')
        );

        return response()->json([
            'rows' => $previewData,
            'total' => count($previewData),
            'valid_count' => count(array_filter($previewData, fn($r) => $r['valid'])),
            'invalid_count' => count(array_filter($previewData, fn($r) => !$r['valid'])),
        ]);
    }

    public function history()
    {
        $imports = ImportHistory::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return inertia('DataImport/History', ['imports' => $imports]);
    }

    public function uploadPddikti(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $file = $request->file('file');

        $result = $this->templateService->importPddikti($file);

        ImportHistory::create([
            'type' => 'dosen_pddikti',
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $file->store('imports/dosen_pddikti'),
            'total_rows' => $result->totalRows,
            'success_rows' => $result->successRows,
            'failed_rows' => $result->failedRows,
            'errors' => $result->errors,
            'user_id' => $request->user()?->id,
            'status' => $result->failedRows > 0 ? 'completed_with_errors' : 'completed',
        ]);

        event(new ImportCompleted(
            userId: $request->user()?->id ?? 0,
            type: 'dosen_pddikti',
            successRows: $result->successRows,
            failedRows: $result->failedRows,
        ));

        if ($result->failedRows > 0) {
            $message = "Import PDDikti selesai. {$result->successRows} baris berhasil, {$result->failedRows} baris gagal.";
            return redirect()->route('data-import.history')
                ->with('warning', $message)
                ->with('import_errors', $result->errors);
        }

        return redirect()->route('data-import.history')
            ->with('success', "Import PDDikti berhasil! {$result->successRows} data dosen telah ditambahkan/diperbarui.");
    }

    public function previewPddikti(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $previewData = $this->templateService->previewPddikti($request->file('file'));

        return response()->json([
            'rows' => $previewData,
            'total' => count($previewData),
            'valid_count' => count(array_filter($previewData, fn($r) => $r['valid'])),
            'invalid_count' => count(array_filter($previewData, fn($r) => !$r['valid'])),
        ]);
    }
}
