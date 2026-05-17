<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = [
            [
                'category' => 'BAN-PT (Standard)',
                'items' => [
                    ['name' => 'Template LED Institusi (AIPT)', 'file' => 'template_led_aipt.xlsx', 'desc' => 'Format standar 4 Aspek BAN-PT 4.0'],
                    ['name' => 'Template LKPT Prodi', 'file' => 'template_lkpt_prodi.xlsx', 'desc' => 'Tabel kriteria 1-9 untuk semua prodi'],
                ]
            ],
            [
                'category' => 'LAM INFOKOM',
                'items' => [
                    ['name' => 'Template Evaluasi Diri (Informatika/TI)', 'file' => 'template_lam_infokom.xlsx', 'desc' => 'Fokus pada OBE & Kurikulum'],
                ]
            ],
            [
                'category' => 'LAMEMBA',
                'items' => [
                    ['name' => 'Template Kinerja Ekonomi/Bisnis', 'file' => 'template_lamemba.xlsx', 'desc' => 'Analisis prospektif & internasionalisasi'],
                ]
            ],
            [
                'category' => 'Seni & Budaya',
                'items' => [
                    ['name' => 'Template Karya & HAKI (Kriya Batik)', 'file' => 'template_kriya_batik.xlsx', 'desc' => 'Pelaporan khusus produk & desain industri'],
                ]
            ],
        ];

        return Inertia::render('Admin/Templates/Index', [
            'templates' => $templates
        ]);
    }

    public function download($filename)
    {
        // For development, we return a response that the file would be here
        // In production, ensure files exist in storage/app/public/templates
        $path = public_path('templates/akreditasi/' . $filename);
        
        if (file_exists($path)) {
            return response()->download($path);
        }

        return redirect()->back()->with('error', 'File template belum tersedia di server. Hubungi Admin IT.');
    }
}
