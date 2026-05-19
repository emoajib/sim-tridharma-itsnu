<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmiCycle extends Model
{
    protected $table = 'spmi_cycles';
    
    protected $fillable = [
        'nama_siklus', 'tanggal_mulai', 'tanggal_selesai', 'keterangan'
    ];
    
    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }
}
