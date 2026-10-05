<?php

namespace App\Models\Platform;

use App\Models\Staff\User;
use Database\Factories\Platform\BillingHandoffTokenFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A single-use, short-lived pass that signs a bakery's owner in on the central
 * billing pages. Only the SHA-256 hash of the token is stored.
 *
 * @property int $id
 * @property string $token_hash
 * @property string $tenant_id
 * @property int $user_id
 * @property Carbon $expires_at
 * @property Carbon|null $consumed_at
 * @property string|null $consumer_ip
 * @property Carbon|null $created_at
 * @property-read Tenant|null $tenant
 * @property-read User $user
 */
#[WithoutTimestamps]
#[Connection('central')]
#[Fillable('token_hash', 'tenant_id', 'user_id', 'expires_at', 'consumed_at', 'consumer_ip', 'created_at')]
#[UseFactory(BillingHandoffTokenFactory::class)]
class BillingHandoffToken extends Model
{
    /** @use HasFactory<BillingHandoffTokenFactory> */
    use HasFactory;

    #[\Override]
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
