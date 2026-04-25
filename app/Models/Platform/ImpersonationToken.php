<?php

namespace App\Models\Platform;

use App\Models\Staff\User;
use Database\Factories\Platform\ImpersonationTokenFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $token
 * @property string $tenant_id
 * @property int|null $created_by_user_id
 * @property Carbon $expires_at
 * @property Carbon|null $consumed_at
 * @property string|null $consumer_ip
 * @property Carbon|null $created_at
 * @property-read Tenant $tenant
 * @property-read User|null $createdBy
 */
#[WithoutTimestamps]
#[Connection('central')]
#[Fillable('token', 'tenant_id', 'created_by_user_id', 'expires_at', 'consumed_at', 'consumer_ip', 'created_at')]
#[UseFactory(ImpersonationTokenFactory::class)]
class ImpersonationToken extends Model
{
    /** @use HasFactory<ImpersonationTokenFactory> */
    use HasFactory;

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
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
