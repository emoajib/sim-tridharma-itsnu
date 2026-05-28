<?php

namespace App\Services\Lppm;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LppmTemplateGenerator
{
    private function createSpreadsheet(string $title, array $headers): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($title);

        foreach ($headers as $col => $label) {
            $cell = $col . '1';
            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        $sheet->getStyle('A1:' . array_key_last($headers) . '1')
            ->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        return $spreadsheet;
    }

    public function generatePenelitianTemplate(string $filePath): string
    {
        $headers = [
            'A' => 'No',
            'B' => 'Judul Penelitian',
            'C' => 'Ketua Pelaksana (NIDN)',
            'D' => 'Anggota (NIDN, pisahkan koma)',
            'E' => 'Skema',
            'F' => 'Sumber Dana',
            'G' => 'Jumlah Dana (Rp)',
            'H' => 'Tahun Pelaksanaan',
            'I' => 'Status',
        ];

        $spreadsheet = $this->createSpreadsheet('Penelitian Hibah Internal', $headers);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(50);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }

    public function generatePkmTemplate(string $filePath): string
    {
        $headers = [
            'A' => 'No',
            'B' => 'Judul PKM',
            'C' => 'Ketua Pelaksana (NIDN)',
            'D' => 'Anggota (NIDN, pisahkan koma)',
            'E' => 'Skema',
            'F' => 'Lokasi Kegiatan',
            'G' => 'Sumber Dana',
            'H' => 'Jumlah Dana (Rp)',
            'I' => 'Tahun Pelaksanaan',
            'J' => 'Status',
        ];

        $spreadsheet = $this->createSpreadsheet('PKM Hibah Internal', $headers);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(50);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(30);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }
}
