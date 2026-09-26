<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Platform;

use App\Models\Platform\Tenant;

final readonly class TenantHealthMetrics
{
    public function __construct(
        public Tenant $tenant,
        public ?string $lastUserActivityAt,
        public int $totalOrders,
        public int $totalProducts,
        public int $totalCategories,
    ) {}
}
