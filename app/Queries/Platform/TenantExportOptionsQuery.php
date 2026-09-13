<?php

namespace App\Queries\Platform;

use App\Models\Platform\Tenant;

final class TenantExportOptionsQuery
{
    private const int CHUNK_SIZE = 100;

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return Tenant::query()
            ->orderBy('store_name')
            ->lazy(self::CHUNK_SIZE)
            ->mapWithKeys(fn (Tenant $tenant): array => [
                $tenant->id => $tenant->store_name ?: $tenant->name,
            ])
            ->all();
    }
}
