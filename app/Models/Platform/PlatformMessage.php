<?php

namespace App\Models\Platform;

use App\Builders\Platform\PlatformMessageQueryBuilder;
use App\Enums\Platform\PlatformSenderType;
use Database\Factories\Platform\PlatformMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $tenant_id
 * @property PlatformSenderType $sender_type
 * @property string $subject
 * @property string $body
 * @property bool $is_read
 * @property Carbon|null $read_at
 * @property int|null $parent_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read PlatformMessage|null $parent
 * @property-read Collection<int, PlatformMessage> $replies
 * @property-read int|null $replies_count
 * @property-read Tenant $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformMessage whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformMessage whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformMessage whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformMessage whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformMessage whereSenderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformMessage whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformMessage whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformMessage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Connection('central')]
#[Fillable('tenant_id', 'parent_id', 'sender_type', 'subject', 'body', 'is_read')]
#[UseEloquentBuilder(PlatformMessageQueryBuilder::class)]
#[UseFactory(PlatformMessageFactory::class)]
class PlatformMessage extends Model
{
    /** @use HasFactory<PlatformMessageFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'sender_type' => PlatformSenderType::class,
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return HasMany<PlatformMessage, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return BelongsTo<PlatformMessage, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
