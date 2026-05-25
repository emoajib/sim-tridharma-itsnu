<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditHistory extends Model
{
    use HasFactory;

    protected $table = 'trx_audit_mutu_histories';

    protected $fillable = [
        'audit_mutu_id', 'user_id', 'field', 'old_value', 'new_value', 'action',
    ];

    public function auditMutu(): BelongsTo
    {
        return $this->belongsTo(AuditMutu::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Convenience helper to create an audit history log entry.
     */
    public static function log(string $action, $entity, ?string $field = null, ?int $userId = null, mixed $oldValue = null, mixed $newValue = null): self
    {
        return static::create([
            'audit_mutu_id' => $entity instanceof AuditMutu ? $entity->id : $entity,
            'user_id' => $userId ?? auth()->id(),
            'field' => $field,
            'old_value' => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
            'new_value' => is_array($newValue) ? json_encode($newValue) : $newValue,
            'action' => $action,
        ]);
    }
}
