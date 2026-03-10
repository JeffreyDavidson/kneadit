<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformActivity extends Model
{
    protected $connection = 'central';

    public $timestamps = false;

    protected $fillable = [
        'event',
        'tenant_id',
        'description',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

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

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
