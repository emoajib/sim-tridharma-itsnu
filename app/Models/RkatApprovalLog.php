<?php

// Vetted by AI - Manual Review Required by Senior Engineer/Manager

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RkatApprovalLog extends Model
{
    use HasFactory;

    protected $table = 'trx_rkat_approval_log';

    protected $fillable = [
        'rkat_id',
        'user_id',
        'action',
        'keterangan',
    ];

    public function rkat(): BelongsTo
    {
        return $this->belongsTo(UsulanRkat::class, 'rkat_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
