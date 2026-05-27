<?php
namespace App\Services\MasterData;

class ImportResult
{
    public function __construct(
        public readonly int $totalRows,
        public readonly int $successRows,
        public readonly int $failedRows,
        public readonly array $errors = [],
    ) {}

    public function toArray(): array
    {
        return [
            'total_rows' => $this->totalRows,
            'success_rows' => $this->successRows,
            'failed_rows' => $this->failedRows,
            'errors' => $this->errors,
        ];
    }
}
