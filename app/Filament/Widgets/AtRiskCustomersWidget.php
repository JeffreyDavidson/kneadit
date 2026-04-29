<?php

namespace App\Filament\Widgets;

use App\Enums\Filament\WidgetSize;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Queries\Customers\AtRiskCustomersQuery;
use Filament\Widgets\Widget;

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
        return AtRiskCustomersQuery::count((int) config('analytics.at_risk_threshold_days', 30)) > 0;
    }

    /** @return array<int, array<string, mixed>> */
    public function getRows(): array
    {
        $threshold = (int) config('analytics.at_risk_threshold_days', 30);

        return AtRiskCustomersQuery::query($threshold)
            ->orderBy('last_order_date')
            ->limit($this->rowLimit())
            ->get()
            ->map(fn ($customer): array => [
                'id' => $customer->id,
                'name' => $customer->name,
                'last_order' => $customer->last_order_date?->diffForHumans() ?? '—',
                'days_inactive' => $customer->days_since_last_order,
                'lifetime_value' => '$' . number_format((float) $customer->lifetime_value, 0),
            ])
            ->all();
    }

    public function getViewAllUrl(): string
    {
        return route('filament.admin.resources.customers.index');
    }

    public function getCustomerEditUrl(int $id): string
    {
        return route('filament.admin.resources.customers.edit', $id);
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
