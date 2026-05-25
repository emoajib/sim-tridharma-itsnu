<?php

namespace App\Events;

use App\Models\AuditMutu;
use Illuminate\Foundation\Events\Dispatchable;

class AuditSevereFindingCreated
{
    use Dispatchable;

    public function __construct(public AuditMutu $audit) {}
}
