<?php

namespace App\Models\Operations;

use App\Enums\Operations\ActivityAction;
use App\Models\Staff\User;
use App\Observers\Platform\ActivityLogObserver;
use Database\Factories\Operations\ActivityLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog query()
 *
 * @mixin \Eloquent
 *
 * @property ActivityAction $action
 * @property \Illuminate\Support\Carbon $created_at
 */
#[WithoutTimestamps]
#[Fillable('user_id', 'user_name', 'action', 'model_type', 'model_id', 'description', 'properties', 'ip_address', 'created_at')]
#[ObservedBy(ActivityLogObserver::class)]
#[UseFactory(ActivityLogFactory::class)]
class ActivityLog extends Model
{
    /** @use HasFactory<ActivityLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'action' => ActivityAction::class,
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
