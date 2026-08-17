<?php

namespace App\Filament\Widgets;

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Customers\Customer;
use App\Support\DatabaseValue;
use DateTimeInterface;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

class ReorderRemindersWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 20;

    protected string $view = 'filament.widgets.reorder-reminders-widget';

    /**
     * Hide entirely when no regulars need a nudge — the "great job"
     * empty state was dead space on a busy dashboard. Reappears the
     * moment a 2+-time customer goes 30+ days without ordering.
     */
    public static function canView(): bool
    {
        $thirtyDaysAgo = Date::now()->subDays(DatabaseValue::int(config('analytics.at_risk_threshold_days'), 30));

        return self::lapsedCustomersQuery($thirtyDaysAgo)->exists();
    }

    /** @return array<int, array<string, string>> */
    public function getLapsedCustomers(): array
    {
        return $this->cached('lapsed_customers', [1800, 3600], function (): array {
            $thirtyDaysAgo = Date::now()->subDays(DatabaseValue::int(config('analytics.at_risk_threshold_days'), 30));

            return self::lapsedCustomersQuery($thirtyDaysAgo)
                ->select('customers.id', 'customers.name', 'customers.email')
                ->orderByDesc('last_order_at')
                ->limit(10)
                ->get()
                ->map(fn (Customer $c) => [
                    'name' => $c->name,
                    'email' => $c->email,
                    'last_order' => $c->last_order_at ? Date::parse($c->last_order_at)->diffForHumans() : 'N/A',
                ])
                ->all();
        });
    }

    public function getLapsedCount(): int
    {
        return $this->cached('lapsed_count', [1800, 3600], function (): int {
            $thirtyDaysAgo = Date::now()->subDays(DatabaseValue::int(config('analytics.at_risk_threshold_days'), 30));

            return self::lapsedCustomersQuery($thirtyDaysAgo)->count();
        });
    }

    /** @return Builder<Customer> */
    private static function lapsedCustomersQuery(DateTimeInterface $cutoff): Builder
    {
        $eligibleOrders = fn (Builder $query): Builder => $query
            ->whereNotIn('status', [OrderStatus::Cancelled->value]);

        return Customer::query()
            ->withMax(['orders as last_order_at' => $eligibleOrders], 'created_at')
            ->whereHas('orders', $eligibleOrders, '>=', 2)
            ->whereDoesntHave('orders', fn (Builder $query): Builder => $eligibleOrders($query)
                ->where('created_at', '>=', $cutoff));
    }

    protected function cachePrefix(): string
    {
        return 'reorder_reminders';
    }
}
