<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentPredictionHistory extends Model
{
    protected $table = 'agent_prediction_history';

    protected $fillable = [
        'prodi_id', 'periode_id', 'skor_prediksi', 'probabilitas_unggul',
        'probabilitas_baik_sekali', 'probabilitas_baik', 'confidence_interval', 'detail_data'
    ];

    protected function casts(): array
    {
        return [
            'skor_prediksi' => 'float',
            'probabilitas_unggul' => 'float',
            'probabilitas_baik_sekali' => 'float',
            'probabilitas_baik' => 'float',
            'confidence_interval' => 'string',
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
}