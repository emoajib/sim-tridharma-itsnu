<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentGeneratorHistory extends Model
{
    protected $table = 'agent_generator_history';

    protected $fillable = [
        'prodi_id', 'periode_id', 'jenis_dokumen', 'judul', 'file_path', 'status', 'prompt_text', 'hasil_text', 'generated_by'
    ];

    protected function casts(): array
    {
        return [
            'hasil_text' => 'array',
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