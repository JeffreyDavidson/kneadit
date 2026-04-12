<?php

namespace App\Models\Platform;

use Database\Factories\Platform\PlatformActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $event
 * @property string|null $tenant_id
 * @property string $description
 * @property array<array-key, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property-read Tenant|null $tenant
 *
 * @method static \Database\Factories\PlatformActivityFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformActivity query()
 *
 * @mixin \Eloquent
 */
#[WithoutTimestamps]
#[Connection('central')]
#[Fillable('event', 'tenant_id', 'description', 'metadata', 'created_at')]
class PlatformActivity extends Model
{
    /** @use HasFactory<PlatformActivityFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    protected static function newFactory(): PlatformActivityFactory
    {
        return PlatformActivityFactory::new();
    }
}
