<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxPemenuhanIndikator extends Model
{
    protected $table = 'trx_pemenuhan_indikator';
    
    protected $fillable = [
        'prodi_id', 'periode_id', 'indikator_id', 'capaian', 'nilai', 'status', 'catatan', 'is_verified'
    ];
    
    protected function casts(): array
    {
        return [
            'nilai' => 'float',
            'is_verified' => 'boolean',
        ];
    }
    
    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }
    
    public function periode()
    {
        return $this->belongsTo(PeriodeAkademik::class);
    }
    
    public function indikator()
    {
        return $this->belongsTo(MIndikatorAkreditasi::class, 'indikator_id');
    }
}