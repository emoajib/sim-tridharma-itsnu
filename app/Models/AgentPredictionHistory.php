<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentPredictionHistory extends Model
{
    protected $table = 'agent_prediction_history';

    protected $fillable = [
        'prodi_id', 'periode_id', 'skor_prediksi', 'prob_unggul',
        'prob_baik_sekali', 'prob_baik', 'confidence_interval'
    ];

    protected function casts(): array
    {
        return [
            'skor_prediksi' => 'float',
            'prob_unggul' => 'float',
            'prob_baik_sekali' => 'float',
            'prob_baik' => 'float',
            'confidence_interval' => 'array',
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