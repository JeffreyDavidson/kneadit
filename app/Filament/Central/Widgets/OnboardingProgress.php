<?php

namespace App\Filament\Central\Widgets;

use App\Models\Tenant;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class OnboardingProgress extends Widget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.central.widgets.onboarding-progress';

    public function getOnboardingStats(): array
    {
        $tenants = Tenant::all();
        $total = $tenants->count();
        $fullyOnboarded = 0;

        foreach ($tenants as $tenant) {
            $completed = $this->countCompleted($tenant);
            if ($completed === 7) {
                $fullyOnboarded++;
            }
        }

        return [
            'onboarded' => $fullyOnboarded,
            'total' => $total,
            'percentage' => $total > 0 ? round(($fullyOnboarded / $total) * 100) : 0,
        ];
    }

    protected function countCompleted(Tenant $tenant): int
    {
        $count = 0;

        if (! empty($tenant->store_name)) {
            $count++;
        }
        if (! empty($tenant->store_logo)) {
            $count++;
        }
        if ($tenant->storefront_enabled) {
            $count++;
        }
        if (! empty($tenant->brand_color_primary) && $tenant->brand_color_primary !== '#d4920c') {
            $count++;
        }

        try {
            tenancy()->initialize($tenant);
            if (DB::table('products')->count() > 0) {
                $count++;
            }
            if (DB::table('categories')->count() > 0) {
                $count++;
            }
            if (DB::table('orders')->count() > 0) {
                $count++;
            }
            tenancy()->end();
        } catch (\Throwable $e) {
            tenancy()->end();
        }

        return $count;
    }
}
