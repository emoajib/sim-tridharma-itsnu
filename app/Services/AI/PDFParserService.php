<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PDFParserService
{
    public function extractText(string $filePath): array
    {
        $fullPath = Storage::disk('public')->path($filePath);

        if (!file_exists($fullPath)) {
            throw new \RuntimeException("File not found: {$fullPath}");
        }

        $text = $this->parseWithSmalot($fullPath);

        if (!$text) {
            $text = $this->fallbackParse($fullPath);
        }

        return [
            'text' => $text,
            'page_count' => $this->countPages($fullPath),
        ];
    }

    protected function parseWithSmalot(string $path): ?string
    {
        if (!class_exists(\Smalot\PdfParser\Parser::class)) {
            return null;
        }

        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();
            $text = preg_replace('/\s+/', ' ', $text);
            return trim($text);
        } catch (\Exception $e) {
            Log::warning('Smalot PDF parser failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function fallbackParse(string $path): string
    {
        try {
            $cmd = escapeshellcmd("pdftotext \"{$path}\" -");
            $output = shell_exec($cmd);
            if ($output) {
                $text = preg_replace('/\s+/', ' ', $output);
                return trim($text);
            }
        } catch (\Exception $e) {
            Log::warning('pdftotext fallback failed', ['error' => $e->getMessage()]);
        }

        return '';
    }

    protected function countPages(string $path): int
    {
        if (!file_exists($path)) return 0;

        $pdfContent = file_get_contents($path);
        preg_match_all('/\/Type\s*\/Page[^s]/i', $pdfContent, $matches);
        $count = count($matches[0] ?? []);

        return max($count, 1);
    }
}
