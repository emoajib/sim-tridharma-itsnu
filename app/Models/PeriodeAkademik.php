<?php

namespace App\Models;

use App\Models\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeriodeAkademik extends Model
{
    use HasActiveScope, HasFactory, SoftDeletes;

    protected $table = 'm_periode_akademik';

    protected $fillable = [
        'kode_periode', 'nama_periode', 'tanggal_mulai', 'tanggal_selesai', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }
}
