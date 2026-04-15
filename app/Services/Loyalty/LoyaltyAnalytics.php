<?php

namespace App\Services\Loyalty;

use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Engagement\LoyaltyReward;
use App\Queries\Loyalty\TopLoyaltyCustomersQuery;
use App\ValueObjects\LoyaltyMetrics;
use Illuminate\Database\Eloquent\Collection;

class LoyaltyAnalytics
{
    private ?LoyaltyMetrics $metricsCache = null;

    public function metrics(): LoyaltyMetrics
    {
        return $this->metricsCache ??= new LoyaltyMetrics(
            totalIssued: (int) LoyaltyPoint::query()->earned()->sum('points'),
            totalRedeemed: (int) LoyaltyPoint::query()->redeemed()->sum('points'),
            activeMembers: LoyaltyPoint::query()->distinct('customer_id')->count('customer_id'),
            availableRewards: LoyaltyReward::query()->active()->count(),
        );
    }

    /**
     * @return Collection<int, Customer>
     */
    public function topCustomers(int $limit = 10): Collection
    {
        return TopLoyaltyCustomersQuery::get($limit);
    }

    /**
     * @return Collection<int, LoyaltyPoint>
     */
    public function recentActivity(int $limit = 15): Collection
    {
        return LoyaltyPoint::with('customer')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function outstandingPoints(): int
    {
        return (int) LoyaltyPoint::query()->sum('points');
    }

    /**
     * @return array<int, array{name: string, points: int}>
     */
    public function leaderboard(int $limit = 5): array
    {
        return $this->topCustomers($limit)
            ->map(fn (Customer $c) => [
                'name' => $c->name,
                'points' => (int) $c->balance,
            ])
            ->all();
    }

    /**
     * @return array<int, array{customer: string, points: int, description: string, date: string}>
     */
    public function recentAwards(int $limit = 3): array
    {
        return LoyaltyPoint::with('customer')
            ->where('points', '>', 0)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (LoyaltyPoint $lp) => [
                'customer' => $lp->customer->name ?? 'Unknown',
                'points' => $lp->points,
                'description' => $lp->description ?? '',
                'date' => $lp->created_at?->diffForHumans() ?? '',
            ])
            ->all();
    }
}
