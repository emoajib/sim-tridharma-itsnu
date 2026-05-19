<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiptMetric extends Model
{
    protected $table = 'aipt_metrics';
    
    protected $fillable = [
        'periode_id', 'aspek', 'skor', 'target', 'keterangan'
    ];
    
    protected function casts(): array
    {
        return [
            'skor' => 'float',
            'target' => 'float',
        ];
    }
    
    public function periode()
    {
        return $this->belongsTo(PeriodeAkademik::class);
    }
}
