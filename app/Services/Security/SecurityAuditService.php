<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Models\SecurityAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecurityAuditService
{
    public function log(
        string $action,
        ?string $description = null,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        string $severity = 'info',
        ?User $user = null,
    ): SecurityAuditLog {
        $user ??= Auth::user();

        $request = request();
        $ipAddress = $request instanceof Request ? $request->ip() : null;
        $userAgent = $request instanceof Request ? $request->userAgent() : null;

        return SecurityAuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'severity' => $severity,
        ]);
    }
}
