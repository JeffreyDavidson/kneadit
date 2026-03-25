<?php

namespace App\Models;

use Database\Factories\PlatformMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $tenant_id
 * @property string $sender_type
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
 * @method static \Database\Factories\PlatformMessageFactory factory($count = null, $state = [])
 * @method static Builder<static>|PlatformMessage fromAdmin()
 * @method static Builder<static>|PlatformMessage fromTenant()
 * @method static Builder<static>|PlatformMessage newModelQuery()
 * @method static Builder<static>|PlatformMessage newQuery()
 * @method static Builder<static>|PlatformMessage query()
 * @method static Builder<static>|PlatformMessage topLevel()
 * @method static Builder<static>|PlatformMessage unread()
 * @method static Builder<static>|PlatformMessage whereBody($value)
 * @method static Builder<static>|PlatformMessage whereCreatedAt($value)
 * @method static Builder<static>|PlatformMessage whereId($value)
 * @method static Builder<static>|PlatformMessage whereIsRead($value)
 * @method static Builder<static>|PlatformMessage whereParentId($value)
 * @method static Builder<static>|PlatformMessage whereReadAt($value)
 * @method static Builder<static>|PlatformMessage whereSenderType($value)
 * @method static Builder<static>|PlatformMessage whereSubject($value)
 * @method static Builder<static>|PlatformMessage whereTenantId($value)
 * @method static Builder<static>|PlatformMessage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PlatformMessage extends Model
{
    /** @use HasFactory<PlatformMessageFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'sender_type',
        'subject',
        'body',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
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

    /** @param Builder<PlatformMessage> $query */
    #[Scope]
    protected function unread(Builder $query): void
    {
        $query->where('is_read', false);
    }

    /** @param Builder<PlatformMessage> $query */
    #[Scope]
    protected function fromAdmin(Builder $query): void
    {
        $query->where('sender_type', 'admin');
    }

    /** @param Builder<PlatformMessage> $query */
    #[Scope]
    protected function fromTenant(Builder $query): void
    {
        $query->where('sender_type', 'tenant');
    }

    /** @param Builder<PlatformMessage> $query */
    #[Scope]
    protected function topLevel(Builder $query): void
    {
        $query->whereNull('parent_id');
    }
}
