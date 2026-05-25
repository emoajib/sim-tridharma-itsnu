<?php

namespace App\Events;

use App\Models\AuditMutu;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class AuditStatusChanged
{
    use Dispatchable;

    public function __construct(
        public AuditMutu $audit,
        public string $oldStatus,
        public string $newStatus,
        public User $user,
    ) {}
}
