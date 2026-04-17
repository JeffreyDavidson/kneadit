<?php

namespace App\Models\Platform;

use Database\Factories\Platform\AdminAuditLogFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $admin_id
 * @property string $action
 * @property string|null $target_type
 * @property string|null $target_id
 * @property string $description
 * @property array<array-key, mixed>|null $metadata
 * @property string|null $ip_address
 * @property Carbon|null $created_at
 *
 * @method static \Database\Factories\AdminAuditLogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog forAction(string $action)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog forTarget(string $type, ?string $id = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog recent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereTargetType($value)
 *
 * @mixin \Eloquent
 */
#[Table('admin_audit_logs')]
#[WithoutTimestamps]
#[Connection('central')]
#[Fillable('admin_id', 'action', 'target_type', 'target_id', 'description', 'metadata', 'ip_address', 'created_at')]
class AdminAuditLog extends Model
{
    /** @use HasFactory<AdminAuditLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Scope: filter by action.
     */
    /** @param Builder<AdminAuditLog> $query */
    #[Scope]
    protected function forAction(Builder $query, string $action): void
    {
        $query->where('action', $action);
    }

    /**
     * Scope: filter by target type and id.
     */
    /** @param Builder<AdminAuditLog> $query */
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
    /** @param Builder<AdminAuditLog> $query */
    #[Scope]
    protected function recent(Builder $query): void
    {
        $query->where('created_at', '>=', now()->subDays(config('analytics.recent_days', 30)));
    }

    protected static function newFactory(): AdminAuditLogFactory
    {
        return AdminAuditLogFactory::new();
    }
}
