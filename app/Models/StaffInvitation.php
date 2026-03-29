<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\StaffInvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property-read User|null $inviter
 *
 * @method static \Database\Factories\StaffInvitationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffInvitation query()
 *
 * @property Carbon|null $expires_at
 *
 * @mixin \Eloquent
 */
class StaffInvitation extends Model
{
    /** @use HasFactory<StaffInvitationFactory> */
    use HasFactory;

    protected $fillable = [
        'email',
        'role',
        'token',
        'accepted_at',
        'expires_at',
        'invited_by',
    ];

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

    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return true;
        }

        return $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return is_null($this->accepted_at) && ! $this->isExpired();
    }
}
