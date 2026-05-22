<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrasiLogSinkron extends Model
{
    use HasFactory;

    protected $table = 'integrasi_log_sinkron';

    protected $fillable = [
        'sumber', 'jenis_data', 'status', 'jumlah_ditarik', 'jumlah_konflik', 'jumlah_diperbarui', 'detail', 'mulai_pada', 'selesai_pada',
    ];

    protected function casts(): array
    {
        return [
            'mulai_pada' => 'datetime',
            'selesai_pada' => 'datetime',
            'jumlah_ditarik' => 'integer',
            'jumlah_konflik' => 'integer',
            'jumlah_diperbarui' => 'integer',
        ];
    }
}
