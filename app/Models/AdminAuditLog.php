<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    protected $connection = 'central';

    protected $table = 'admin_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'action',
        'target_type',
        'target_id',
        'description',
        'metadata',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Log an admin action.
     */
    public static function log(
        string $action,
        string $description,
        ?string $targetType = null,
        ?string $targetId = null,
        ?array $metadata = null,
    ): static {
        return static::create([
            'admin_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    /**
     * Scope: filter by action.
     */
    public function scopeForAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: filter by target type and id.
     */
    public function scopeForTarget(Builder $query, string $type, ?string $id = null): Builder
    {
        $query->where('target_type', $type);

        if ($id !== null) {
            $query->where('target_id', $id);
        }

        return $query;
    }

    /**
     * Scope: last 30 days.
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }
}
