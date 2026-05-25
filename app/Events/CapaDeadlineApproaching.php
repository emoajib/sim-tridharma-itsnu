<?php

namespace App\Events;

use App\Models\Capa;
use Illuminate\Foundation\Events\Dispatchable;

class CapaDeadlineApproaching
{
    use Dispatchable;

    public function __construct(public Capa $capa) {}
}
