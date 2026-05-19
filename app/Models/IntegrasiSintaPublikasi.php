<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrasiSintaPublikasi extends Model
{
    protected $table = 'integrasi_sinta_publikasi';
    
    protected $fillable = [
        'dosen_id', 'publikasi_id', 'judul', 'data_dari_sinta', 'status_sinkron', 'resolved_at'
    ];
    
    protected function casts(): array
    {
        return [
            'data_dari_sinta' => 'array',
            'resolved_at' => 'datetime',
        ];
    }
    
    public function dosen()
    {
        return $this->belongsTo(MDosen::class);
    }
    
    public function publikasi()
    {
        return $this->belongsTo(TrxPublikasi::class);
    }
}