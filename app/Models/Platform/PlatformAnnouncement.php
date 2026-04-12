<?php

namespace App\Models\Platform;

use App\Enums\Platform\AnnouncementType;
use Database\Factories\Platform\PlatformAnnouncementFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $body
 * @property string $type
 * @property array<array-key, mixed>|null $target_plans
 * @property bool $is_active
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property bool $is_dismissable
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder<static>|PlatformAnnouncement active()
 * @method static \Database\Factories\PlatformAnnouncementFactory factory($count = null, $state = [])
 * @method static Builder<static>|PlatformAnnouncement newModelQuery()
 * @method static Builder<static>|PlatformAnnouncement newQuery()
 * @method static Builder<static>|PlatformAnnouncement query()
 * @method static Builder<static>|PlatformAnnouncement whereBody($value)
 * @method static Builder<static>|PlatformAnnouncement whereCreatedAt($value)
 * @method static Builder<static>|PlatformAnnouncement whereEndsAt($value)
 * @method static Builder<static>|PlatformAnnouncement whereId($value)
 * @method static Builder<static>|PlatformAnnouncement whereIsActive($value)
 * @method static Builder<static>|PlatformAnnouncement whereIsDismissable($value)
 * @method static Builder<static>|PlatformAnnouncement whereStartsAt($value)
 * @method static Builder<static>|PlatformAnnouncement whereTargetPlans($value)
 * @method static Builder<static>|PlatformAnnouncement whereTitle($value)
 * @method static Builder<static>|PlatformAnnouncement whereType($value)
 * @method static Builder<static>|PlatformAnnouncement whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Connection('central')]
#[Fillable('title', 'body', 'type', 'target_plans', 'is_active', 'starts_at', 'ends_at', 'is_dismissable')]
class PlatformAnnouncement extends Model
{
    /** @use HasFactory<PlatformAnnouncementFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => AnnouncementType::class,
            'target_plans' => 'array',
            'is_active' => 'boolean',
            'is_dismissable' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /** @param Builder<PlatformAnnouncement> $query */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query
            ->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    protected static function newFactory(): PlatformAnnouncementFactory
    {
        return PlatformAnnouncementFactory::new();
    }
}
