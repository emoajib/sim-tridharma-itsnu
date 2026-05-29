<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalKegiatan extends Model
{
    use HasFactory;

    protected $table = 'trx_proposal_kegiatan';

    protected $fillable = [
        'jenis_proposal',
        'ormawa_id',
        'prodi_id',
        'periode_id',
        'judul_kegiatan',
        'latar_belakang',
        'tanggal_mulai',
        'tanggal_selesai',
        'rab_diajukan',
        'rab_disetujui',
        'file_proposal',
        'file_lpj',
        'status_kegiatan',
        'status_hima',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'rab_diajukan' => 'decimal:2',
            'rab_disetujui' => 'decimal:2',
        ];
    }

    public function ormawa()
    {
        return $this->belongsTo(Ormawa::class);
    }

    public function periode()
    {
        return $this->belongsTo(PeriodeAkademik::class);
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }
}
