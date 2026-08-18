<?php

namespace App\Filament\Widgets;

use App\Enums\Filament\WidgetSize;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Customers\Customer;
use App\Queries\Customers\AtRiskCustomersQuery;
use DateTimeInterface;
use Filament\Widgets\Widget;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class AtRiskCustomersWidget extends Widget
{
    use HasDashboardSize;

    protected static ?int $sort = 10;

    protected string $view = 'filament.widgets.at-risk-customers';

    /**
     * Hide entirely when no customers are at risk — "everyone's
     * recently active" empty state was just dead space. Reappears
     * the moment any customer crosses the inactivity threshold.
     */
    public static function canView(): bool
    {
        return AtRiskCustomersQuery::count(Config::integer('analytics.at_risk_threshold_days', 30)) > 0;
    }

    /** @return array<int, array{id: int, name: string, last_order: string, days_inactive: int, lifetime_value: string}> */
    public function getRows(): array
    {
        $threshold = Config::integer('analytics.at_risk_threshold_days', 30);

        return AtRiskCustomersQuery::query($threshold)
            ->orderBy('last_order_date')
            ->limit($this->rowLimit())
            ->get()
            ->map(function (Customer $customer): array {
                $lastOrderDate = $customer->getAttribute('last_order_date');
                $lastOrder = is_string($lastOrderDate) || $lastOrderDate instanceof DateTimeInterface
                    ? Carbon::parse($lastOrderDate)->diffForHumans()
                    : 'Never';

                $attributes = $customer->getAttributes();

                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'last_order' => $lastOrder,
                    'days_inactive' => Arr::integer($attributes, 'days_since_last_order', 0),
                    'lifetime_value' => '$' . number_format(Arr::float($attributes, 'lifetime_value', 0.0), 0),
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
