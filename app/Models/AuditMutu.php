<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditMutu extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_audit_mutu';

    protected $fillable = [
        'prodi_id', 'periode_id', 'standar_mutu_id',
        'judul_audit', 'tanggal_audit', 'auditor',
        'temuan', 'rekomendasi', 'status', 'tindak_lanjut',
        'severity', 'pic_user_id', 'auditor_user_id',
        'deadline_tindak_lanjut', 'closed_at',
        'evidence_file', 'verification_note', 'verified_by', 'verified_at',
        'is_locked', 'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_audit' => 'date',
            'deadline_tindak_lanjut' => 'date',
            'closed_at' => 'datetime',
            'verified_at' => 'datetime',
            'locked_at' => 'datetime',
            'is_locked' => 'boolean',
            'severity' => 'string',
        ];
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_id');
    }

    public function standarMutu(): BelongsTo
    {
        return $this->belongsTo(StandarMutu::class);
    }

    public function picUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_user_id');
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function capas(): HasMany
    {
        return $this->hasMany(Capa::class, 'audit_mutu_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(AuditHistory::class, 'audit_mutu_id');
    }
}
