<?php

namespace App\Actions\Platform;

use App\Models\PlatformActivity;

class LogPlatformActivity
{
    /** @param array<string, mixed>|null $metadata */
    public function __invoke(string $event, ?string $tenantId, string $description, ?array $metadata = null): PlatformActivity
    {
        return PlatformActivity::query()->create([
            'event' => $event,
            'tenant_id' => $tenantId,
            'description' => $description,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
