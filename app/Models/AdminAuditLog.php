<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    use HasFactory;

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
    #[Scope]
    protected function forAction(Builder $query, string $action): void
    {
        $query->where('action', $action);
    }

    /**
     * Scope: filter by target type and id.
     */
    #[Scope]
    protected function forTarget(Builder $query, string $type, ?string $id = null): void
    {
        $query->where('target_type', $type);

        if ($id !== null) {
            $query->where('target_id', $id);
        }
    }

    /**
     * Scope: last 30 days.
     */
    #[Scope]
    protected function recent(Builder $query): void
    {
        $query->where('created_at', '>=', now()->subDays(30));
    }
}
