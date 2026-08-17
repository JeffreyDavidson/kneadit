<?php

namespace App\Events\Platform;

use App\Models\Orders\OrderItem;
use App\Models\Staff\User;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Collection;

class WeeklyDigestRequested implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    /**
     * @param array<string, mixed> $stats
     * @param Collection<int, OrderItem> $topProducts
     * @param Collection<int, array{name: string, days_since_last_order: ?int}> $atRiskCustomers
     */
    public function __construct(
        public readonly User $user,
        public readonly array $stats,
        public readonly Collection $topProducts,
        public readonly Collection $atRiskCustomers,
        public readonly int $upcomingCount,
        public readonly string $storeName,
        public readonly string $adminUrl,
    ) {}
}
