<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Capa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trx_capa';

    protected $fillable = [
        'audit_mutu_id', 'pic_user_id', 'verified_by_user_id',
        'root_cause_category', 'root_cause_analysis',
        'corrective_action', 'corrective_deadline', 'corrective_completed_at', 'corrective_evidence_file',
        'preventive_action', 'preventive_deadline', 'preventive_completed_at', 'preventive_evidence_file',
        'status', 'verification_note', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'corrective_deadline' => 'date',
            'corrective_completed_at' => 'date',
            'preventive_deadline' => 'date',
            'preventive_completed_at' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function auditMutu(): BelongsTo
    {
        return $this->belongsTo(AuditMutu::class);
    }

    public function picUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}
