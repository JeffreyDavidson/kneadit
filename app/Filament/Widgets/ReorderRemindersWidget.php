<?php

namespace App\Filament\Widgets;

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Customers\Customer;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
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
        return self::lapsedCustomersQuery()->exists();
    }

    /** @return array<int, array<string, string>> */
    public function getLapsedCustomers(): array
    {
        return $this->cached('lapsed_customers', [1800, 3600], function (): array {
            return self::lapsedCustomersQuery()
                ->withMax(['orders as last_order_at' => self::eligibleOrders(...)], 'created_at')
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
            return self::lapsedCustomersQuery()->count();
        });
    }

    /** @return Builder<Customer> */
    private static function lapsedCustomersQuery(): Builder
    {
        $cutoff = Date::now()->subDays(Config::integer('analytics.at_risk_threshold_days', 30));

        return Customer::query()
            ->select(['customers.id', 'customers.name', 'customers.email'])
            ->whereHas('orders', self::eligibleOrders(...), '>=', 2)
            ->whereDoesntHave('orders', function (Builder $query) use ($cutoff): void {
                self::eligibleOrders($query);
                $query->where('created_at', '>=', $cutoff);
            });
    }

    /** @param Builder<Model> $query */
    private static function eligibleOrders(Builder $query): void
    {
        $query->where('status', '!=', OrderStatus::Cancelled);
    }

    protected function cachePrefix(): string
    {
        return 'reorder_reminders';
    }
}
