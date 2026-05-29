<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prestasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_prestasi';

    protected $fillable = [
        'kategori_id',
        'nama_kompetisi',
        'penyelenggara',
        'tanggal_pelaksanaan',
        'tingkat',
        'peringkat',
        'bukti_url',
        'file_sertifikat',
        'status_verifikasi',
        'catatan_reviewer',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pelaksanaan' => 'date',
            'status_verifikasi' => 'string',
        ];
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriPrestasi::class);
    }

    public function members()
    {
        return $this->hasMany(PrestasiMember::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
