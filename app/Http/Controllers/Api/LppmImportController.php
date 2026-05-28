<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Lppm\LppmImportService;
use App\Services\Lppm\LppmTemplateGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LppmImportController extends Controller
{
    public function __construct(
        private readonly LppmImportService $importService,
        private readonly LppmTemplateGenerator $templateGenerator,
    ) {}

    public function index()
    {
        return Inertia::render('Agent/Integrasi/LppmImport');
    }

    public function importPenelitian(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $result = $this->importService->importPenelitian(
            $request->file('file'),
            $request->user()?->id,
        );

        if ($result['status'] === 'error') {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', 'Import penelitian LPPM berhasil');
    }

    public function importPkm(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $result = $this->importService->importPkm(
            $request->file('file'),
            $request->user()?->id,
        );

        if ($result['status'] === 'error') {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', 'Import PKM LPPM berhasil');
    }

    public function downloadTemplate(string $type)
    {
        $types = ['penelitian', 'pkm'];
        if (!in_array($type, $types)) {
            abort(404);
        }

        $path = storage_path("app/templates/template_import_lppm_{$type}.xlsx");

        if (!file_exists($path)) {
            $method = 'generate' . ucfirst($type) . 'Template';
            $this->templateGenerator->{$method}($path);
        }

        return response()->download($path, "template_import_lppm_{$type}.xlsx");
    }

    public function downloadTemplatePenelitian()
    {
        return $this->downloadTemplate('penelitian');
    }

    public function downloadTemplatePkm()
    {
        return $this->downloadTemplate('pkm');
    }
}
