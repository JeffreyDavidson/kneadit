<?php

namespace App\Models\Platform;

use App\Builders\Platform\PlatformAnnouncementQueryBuilder;
use App\Enums\Platform\AnnouncementType;
use Database\Factories\Platform\PlatformAnnouncementFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $body
 * @property AnnouncementType $type
 * @property array<array-key, mixed>|null $target_plans
 * @property bool $is_active
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property bool $is_dismissable
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformAnnouncement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformAnnouncement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformAnnouncement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformAnnouncement whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformAnnouncement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformAnnouncement whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformAnnouncement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformAnnouncement whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformAnnouncement whereIsDismissable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformAnnouncement whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformAnnouncement whereTargetPlans($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformAnnouncement whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformAnnouncement whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformAnnouncement whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Connection('central')]
#[Fillable('title', 'body', 'type', 'target_plans', 'is_active', 'starts_at', 'ends_at', 'is_dismissable')]
#[UseEloquentBuilder(PlatformAnnouncementQueryBuilder::class)]
#[UseFactory(PlatformAnnouncementFactory::class)]
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
}
