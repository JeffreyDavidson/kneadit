<?php

declare(strict_types=1);

namespace App\Actions\Platform;

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantNote;

final class AddTenantNote
{
    public function __invoke(Tenant $tenant, string $body, string $author): TenantNote
    {
        return $tenant->notes()->create([
            'body' => $body,
            'author' => $author,
        ]);
    }
}
