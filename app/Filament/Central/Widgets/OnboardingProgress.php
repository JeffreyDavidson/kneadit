<?php

namespace App\Filament\Central\Widgets;

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantOnboardingMetrics;
use Filament\Widgets\Widget;

class OnboardingProgress extends Widget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.central.widgets.onboarding-progress';

    /** @return array<string, mixed> */
    public function getOnboardingStats(): array
    {
        $tenants = Tenant::all();
        $total = $tenants->count();
        $fullyOnboarded = 0;

        foreach ($tenants as $tenant) {
            $completed = $this->countCompleted($tenant);
            if ($completed === TenantOnboardingMetrics::TOTAL_CHECKS) {
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
        return resolve(TenantOnboardingMetrics::class)->completed($tenant);
    }
}
