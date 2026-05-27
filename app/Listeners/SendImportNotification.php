<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ImportCompleted;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SendImportNotification
{
    public function handle(ImportCompleted $event): void
    {
        Log::info("Import completed for user {$event->userId}", [
            'type' => $event->type,
            'success_rows' => $event->successRows,
            'failed_rows' => $event->failedRows,
        ]);
    }
}
