<?php

namespace App\Models\Platform;

use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $tenant_id
 * @property int|null $granted_by_user_id
 * @property \Illuminate\Support\Carbon $granted_at
 * @property \Illuminate\Support\Carbon|null $revoked_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Tenant $tenant
 * @property-read User|null $grantedByUser
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreeForeverGrant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreeForeverGrant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreeForeverGrant query()
 *
 * @mixin \Eloquent
 */
#[Fillable('tenant_id', 'granted_by_user_id', 'granted_at', 'revoked_at')]
class FreeForeverGrant extends Model
{
    protected function casts(): array
    {
        return [
            'granted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return BelongsTo<User, $this> */
    public function grantedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by_user_id');
    }
}
