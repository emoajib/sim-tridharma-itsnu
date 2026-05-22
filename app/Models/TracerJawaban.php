<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TracerJawaban extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_tracer_jawaban';

    protected $fillable = [
        'alumni_id', 'kuisioner_id', 'jawaban', 'diisi_pada',
    ];

    protected function casts(): array
    {
        return [
            'jawaban' => 'json',
            'diisi_pada' => 'datetime',
        ];
    }

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }

    public function kuisioner(): BelongsTo
    {
        return $this->belongsTo(KuisionerTracer::class, 'kuisioner_id');
    }
}
