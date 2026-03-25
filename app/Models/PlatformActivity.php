<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformActivity extends Model
{
    use HasFactory;

    protected $connection = 'central';

    public $timestamps = false;

    protected $fillable = [
        'event',
        'tenant_id',
        'description',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public static function log(string $event, ?string $tenantId, string $description, ?array $metadata = null): static
    {
        return static::create([
            'event' => $event,
            'tenant_id' => $tenantId,
            'description' => $description,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
