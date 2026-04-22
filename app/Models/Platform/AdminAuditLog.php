<?php

namespace App\Models\Platform;

use App\Builders\Platform\AdminAuditLogQueryBuilder;
use Database\Factories\Platform\AdminAuditLogFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog query()
 *
 * @mixin \Eloquent
 */
#[Table('admin_audit_logs')]
#[WithoutTimestamps]
#[Connection('central')]
#[Fillable('admin_id', 'action', 'target_type', 'target_id', 'description', 'metadata', 'ip_address', 'created_at')]
#[UseEloquentBuilder(AdminAuditLogQueryBuilder::class)]
#[UseFactory(AdminAuditLogFactory::class)]
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
}
