<?php

declare(strict_types=1);

namespace App\Actions\Platform;

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantNote;

final class DeleteTenantNote
{
    public function __invoke(Tenant $tenant, int $noteId): void
    {
        TenantNote::query()
            ->where('tenant_id', $tenant->id)
            ->whereKey($noteId)
            ->delete();
    }
}
