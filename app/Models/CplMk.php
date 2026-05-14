<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CplMk extends Pivot
{
    protected $table = 'm_cpl_mk';

    protected $fillable = [
        'cpl_id', 'mata_kuliah_id', 'tingkat',
    ];
}
