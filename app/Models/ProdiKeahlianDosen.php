<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdiKeahlianDosen extends Model
{
    protected $table = 'trx_prodi_keahlian_dosen';
    
    protected $fillable = [
        'dosen_id', 'prodi_keahlian_id', 'is_utama'
    ];
    
    protected function casts(): array
    {
        return [
            'is_utama' => 'boolean',
        ];
    }
    
    public function dosen()
    {
        return $this->belongsTo(MDosen::class);
    }
    
    public function prodiKeahlian()
    {
        return $this->belongsTo(ProdiKeahlian::class, 'prodi_keahlian_id');
    }
}