<?php

namespace App\Models;

use Database\Factories\TenantNoteFactory;
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
 * @method static \Database\Factories\TenantNoteFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantNote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantNote whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantNote whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantNote whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantNote whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class TenantNote extends Model
{
    /** @use HasFactory<TenantNoteFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = ['tenant_id', 'body', 'author'];

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
