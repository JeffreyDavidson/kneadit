<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Platform;

use App\Models\Platform\Tenant;

final readonly class TenantChurnMetrics
{
    public function __construct(
        public Tenant $tenant,
        public ?TenantHealthMetrics $healthMetrics,
        public ?int $recentOrderCount,
    ) {}
}
