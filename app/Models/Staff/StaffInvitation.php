<?php

namespace App\Models\Staff;

use App\Enums\Staff\UserRole;
use Database\Factories\Staff\StaffInvitationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property UserRole $role
 * @property-read User|null $inviter
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffInvitation query()
 *
 * @property Carbon|null $expires_at
 *
 * @mixin \Eloquent
 */
#[Fillable('email', 'role', 'token', 'accepted_at', 'expires_at', 'invited_by')]
class StaffInvitation extends Model
{
    /** @use HasFactory<StaffInvitationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'expires_at' => 'datetime',
            'role' => UserRole::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /** @param  Builder<StaffInvitation>  $query */
    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->whereNull('accepted_at');
    }

    /** @return Attribute<bool, never> */
    protected function isExpired(): Attribute
    {
        return Attribute::make(
            get: fn () => ! $this->expires_at || $this->expires_at->isPast(),
        );
    }

    /** @return Attribute<bool, never> */
    protected function isPending(): Attribute
    {
        return Attribute::make(
            get: fn () => is_null($this->accepted_at) && ! $this->is_expired,
        );
    }

    protected static function newFactory(): StaffInvitationFactory
    {
        return StaffInvitationFactory::new();
    }
}
