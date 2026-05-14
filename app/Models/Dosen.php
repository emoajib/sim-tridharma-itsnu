<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dosen extends Model
{
    use SoftDeletes;

    protected $table = 'm_dosen';

    protected $fillable = [
        'nidn', 'nip', 'nama_depan', 'nama_belakang', 'gelar_depan', 'gelar_belakang',
        'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'prodi_id',
        'pendidikan_terakhir', 'jabatan_fungsional', 'status_aktivitas',
        'email', 'telepon', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'tanggal_lahir' => 'date',
        ];
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }
}
