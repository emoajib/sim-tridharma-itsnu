<?php

namespace App\Models;

use App\Models\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KuisionerTracer extends Model
{
    use HasActiveScope, SoftDeletes;

    protected $table = 'm_kuisioner_tracer';

    protected $fillable = [
        'prodi_id', 'judul_kuisioner', 'tahun', 'pertanyaan', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'pertanyaan' => 'json',
        ];
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }
}
