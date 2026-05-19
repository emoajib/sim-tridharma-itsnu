<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrasiPddiktiDosen extends Model
{
    protected $table = 'integrasi_pddikti_dosen';
    
    protected $fillable = [
        'dosen_id', 'nidn', 'data_dari_pddikti', 'data_di_sistem', 'status_sinkron', 'resolved_at'
    ];
    
    protected function casts(): array
    {
        return [
            'data_dari_pddikti' => 'array',
            'data_di_sistem' => 'array',
            'resolved_at' => 'datetime',
        ];
    }
    
    public function dosen()
    {
        return $this->belongsTo(MDosen::class);
    }
}