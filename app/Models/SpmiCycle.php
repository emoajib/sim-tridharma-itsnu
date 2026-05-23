<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpmiCycle extends Model
{
    use HasFactory;

    protected $table = 'spmi_cycles';

    protected $fillable = [
        'tahap', 'kategori', 'nama_siklus', 'tanggal_mulai', 'tanggal_selesai',
        'persentase_selesai', 'status', 'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'persentase_selesai' => 'float',
        ];
    }
}
