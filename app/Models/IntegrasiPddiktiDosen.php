<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrasiPddiktiDosen extends Model
{
    use HasFactory;

    protected $table = 'integrasi_pddikti_dosen';

    protected $fillable = [
        'dosen_id', 'nidn', 'data_dari_pddikti', 'data_di_sistem', 'status_sinkron', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'data_dari_pddikti' => 'array',
            'data_di_sistem' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }
}
