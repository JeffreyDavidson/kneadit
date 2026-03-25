<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use Filament\Widgets\Widget;

class LoyaltyLeadersWidget extends Widget
{
    protected static ?int $sort = 21;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.loyalty-leaders-widget';

    public function getTopCustomers(): array
    {
        return Customer::query()->select('customers.id', 'customers.name')
            ->selectRaw('SUM(loyalty_points.points) as total_points')
            ->join('loyalty_points', 'loyalty_points.customer_id', '=', 'customers.id')
            ->groupBy('customers.id', 'customers.name')
            ->orderByRaw('SUM(loyalty_points.points) DESC')
            ->limit(5)
            ->get()
            ->map(fn (Customer $c) => [
                'name' => $c->name,
                'points' => (int) $c->total_points,
            ])
            ->all();
    }

    public function getTotalPointsOutstanding(): int
    {
        return (int) LoyaltyPoint::query()->sum('points');
    }

    public function getRecentAwards(): array
    {
        return LoyaltyPoint::with('customer')
            ->where('points', '>', 0)->latest()
            ->limit(3)
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
