<?php

declare(strict_types=1);

namespace App\Actions\Platform;

use App\Models\Platform\Tenant;

class PauseTenant
{
    public function __invoke(Tenant $tenant): void
    {
        if ($tenant->is_paused) {
            return;
        }

        $tenant->update(['paused_at' => now()]);
    }
}
