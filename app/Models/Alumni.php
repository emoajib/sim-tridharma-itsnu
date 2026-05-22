<?php

namespace App\Models;

use App\Models\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alumni extends Model
{
    use HasActiveScope, SoftDeletes;

    protected $table = 'm_alumni';

    protected $fillable = [
        'mahasiswa_id', 'nim', 'nama', 'prodi_id', 'tahun_lulus',
        'masa_tunggu', 'gaji_pertama', 'pekerjaan', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'gaji_pertama' => 'decimal:2',
            'masa_tunggu' => 'integer',
        ];
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
