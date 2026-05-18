<?php

namespace App\Services\AI;

class ChunkerService
{
    public function chunk(string $text, int $maxLength = 500, int $overlap = 50): array
    {
        $text = trim($text);
        if (empty($text)) return [];

        $sentences = preg_split('/(?<=[.!?])\s+/', $text);
        $chunks = [];
        $current = '';

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (empty($sentence)) continue;

            if (mb_strlen($current . ' ' . $sentence) <= $maxLength) {
                $current .= ($current ? ' ' : '') . $sentence;
            } else {
                if ($current) $chunks[] = $current;
                $current = $sentence;
            }
        }

        if ($current) $chunks[] = $current;

        if (count($chunks) <= 1) return $chunks;

        if ($overlap > 0 && count($chunks) > 1) {
            $merged = [];
            $last = '';
            foreach ($chunks as $chunk) {
                $merged[] = $last ? $last . ' ' . $chunk : $chunk;
                $words = explode(' ', $chunk);
                $last = implode(' ', array_slice($words, 0, min($overlap, count($words))));
            }
            $chunks = $merged;
        }

        return $chunks;
    }
}
