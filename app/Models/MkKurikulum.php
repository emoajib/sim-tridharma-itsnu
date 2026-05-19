<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MkKurikulum extends Model
{
    protected $table = 'm_mk_kurikulum';
    
    protected $fillable = [
        'kurikulum_id', 'mata_kuliah_id', 'semester_rekomendasi'
    ];
    
    public function kurikulum()
    {
        return $this->belongsTo(MKurikulum::class);
    }
    
    public function mataKuliah()
    {
        return $this->belongsTo(MMataKuliah::class, 'mata_kuliah_id');
    }
}