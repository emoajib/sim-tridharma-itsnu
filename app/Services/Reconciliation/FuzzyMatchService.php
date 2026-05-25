<?php

namespace App\Services\Reconciliation;

class FuzzyMatchService
{
    public function match(string $input, array $targets, float $threshold = 0.6): array
    {
        $results = [];

        foreach ($targets as $key => $target) {
            $similarity = $this->similarity($input, $target);
            if ($similarity >= $threshold) {
                $results[] = [
                    'key' => $key,
                    'target' => $target,
                    'similarity' => $similarity,
                ];
            }
        }

        usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return $results;
    }

    public function similarity(string $a, string $b): float
    {
        $a = mb_strtolower(trim($a));
        $b = mb_strtolower(trim($b));

        if ($a === $b) {
            return 1.0;
        }

        $lenA = mb_strlen($a);
        $lenB = mb_strlen($b);

        if ($lenA === 0 || $lenB === 0) {
            return 0.0;
        }

        $distance = $this->levenshteinDistance($a, $b);
        $maxLen = max($lenA, $lenB);

        return 1.0 - ($distance / $maxLen);
    }

    private function levenshteinDistance(string $a, string $b): int
    {
        $lenA = mb_strlen($a);
        $lenB = mb_strlen($b);

        if ($lenA === 0) return $lenB;
        if ($lenB === 0) return $lenA;

        $matrix = [];
        for ($i = 0; $i <= $lenB; $i++) {
            $matrix[$i] = [$i];
        }
        for ($j = 0; $j <= $lenA; $j++) {
            $matrix[0][$j] = $j;
        }

        for ($i = 1; $i <= $lenB; $i++) {
            for ($j = 1; $j <= $lenA; $j++) {
                $cost = mb_substr($a, $j - 1, 1) === mb_substr($b, $i - 1, 1) ? 0 : 1;
                $matrix[$i][$j] = min(
                    $matrix[$i - 1][$j] + 1,
                    $matrix[$i][$j - 1] + 1,
                    $matrix[$i - 1][$j - 1] + $cost
                );
            }
        }

        return $matrix[$lenB][$lenA];
    }
}
