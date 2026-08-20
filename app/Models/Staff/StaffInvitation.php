<?php

namespace App\Models\Staff;

use App\Builders\Staff\StaffInvitationQueryBuilder;
use App\Enums\Staff\UserRole;
use Database\Factories\Staff\StaffInvitationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property UserRole $role
 * @property-read User|null $inviter
 *
 * @method static StaffInvitationQueryBuilder newModelQuery()
 * @method static StaffInvitationQueryBuilder newQuery()
 * @method static StaffInvitationQueryBuilder query()
 *
 * @property Carbon $expires_at
 *
 * @mixin \Eloquent
 */
#[Fillable('email', 'role', 'token', 'accepted_at', 'expires_at', 'invited_by')]
#[UseEloquentBuilder(StaffInvitationQueryBuilder::class)]
#[UseFactory(StaffInvitationFactory::class)]
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
}
