<?php

namespace App\Filament\Widgets;

use App\Enums\Filament\WidgetSize;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Customers\Customer;
use App\Queries\Customers\AtRiskCustomersQuery;
use App\ValueObjects\Money;
use DateTimeInterface;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class AtRiskCustomersWidget extends Widget
{
    use HasDashboardSize;

    #[\Override]
    protected static ?int $sort = 10;

    #[\Override]
    protected string $view = 'filament.widgets.at-risk-customers';

    /**
     * Hide entirely when no customers are at risk — "everyone's
     * recently active" empty state was just dead space. Reappears
     * the moment any customer crosses the inactivity threshold.
     */
    #[\Override]
    public static function canView(): bool
    {
        return AtRiskCustomersQuery::count(Config::integer('analytics.at_risk_threshold_days', 30)) > 0;
    }

    /** @return array<int, array{id: int, name: string, last_order: string, days_inactive: int, lifetime_value: string}> */
    public function getRows(): array
    {
        $threshold = Config::integer('analytics.at_risk_threshold_days', 30);

        return AtRiskCustomersQuery::query($threshold)
            ->withOrderMetrics()
            ->orderBy('last_order_date')
            ->limit($this->rowLimit())
            ->get()
            ->map(function (Customer $customer): array {
                $lastOrderDate = $customer->getAttribute('last_order_date');
                $lastOrderAt = is_string($lastOrderDate) || $lastOrderDate instanceof DateTimeInterface
                    ? Carbon::parse($lastOrderDate)
                    : null;
                $lifetimeValue = $customer->getAttribute('orders_sum_total');

                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'last_order' => $lastOrderAt?->diffForHumans() ?? 'Never',
                    'days_inactive' => (int) ($lastOrderAt?->diffInDays(now()) ?? 0),
                    'lifetime_value' => '$'.number_format(Money::fromCents(is_numeric($lifetimeValue) ? (int) $lifetimeValue : 0)->dollars(), 0),
                ];
            })
            ->all();
    }

    public function getViewAllUrl(): string
    {
        return route('filament.admin.resources.customers.index');
    }

    public function getCustomerViewUrl(int $id): string
    {
        return route('filament.admin.resources.customers.view', $id);
    }

    private function rowLimit(): int
    {
        return match ($this->size()) {
            WidgetSize::Small => 3,
            WidgetSize::Medium => 5,
            default => 10,
        };
    }
}
