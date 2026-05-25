<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpmiCycle extends Model
{
    use HasFactory;

    protected $table = 'spmi_cycles';

    protected $fillable = [
        'prodi_id', 'periode_id', 'instrumen_id', 'tahap', 'kategori', 'nama_siklus', 'tanggal_mulai', 'tanggal_selesai',
        'persentase_selesai', 'status', 'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'persentase_selesai' => 'float',
        ];
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_id');
    }

    public function instrumen(): BelongsTo
    {
        return $this->belongsTo(InstrumenAkreditasi::class, 'instrumen_id');
    }
}
