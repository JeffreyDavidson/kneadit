<?php

namespace App\Models\Platform;

use Database\Factories\Platform\TenantNoteFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $tenant_id
 * @property string $body
 * @property string $author
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Tenant $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantNote query()
 *
 * @mixin \Eloquent
 */
#[Connection('central')]
#[Fillable('tenant_id', 'body', 'author')]
#[UseFactory(TenantNoteFactory::class)]
class TenantNote extends Model
{
    /** @use HasFactory<TenantNoteFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
