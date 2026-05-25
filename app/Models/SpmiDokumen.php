<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpmiDokumen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_spmi_dokumen';

    protected $fillable = [
        'kategori', 'nomor_dokumen', 'judul', 'deskripsi',
        'file_path', 'file_original_name', 'version',
        'approved_by', 'approved_at', 'tanggal_berlaku', 'tanggal_kadaluarsa',
        'status', 'catatan_revisi',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'approved_at' => 'datetime',
            'tanggal_berlaku' => 'date',
            'tanggal_kadaluarsa' => 'date',
        ];
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
