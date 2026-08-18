<?php

namespace App\Filament\Central\Pages;

use App\DataTransferObjects\Settings\BrandingSettings;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class OnboardingTracker extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Onboarding Tracker';

    protected string $view = 'filament.central.pages.onboarding-tracker';

    public function getSubheading(): ?string
    {
        return 'Monitor which bakers have completed their setup.';
    }

    public string $filterStatus = 'all';

    public string $filterPlan = 'all';

    public string $sort = 'progress_asc';

    /** @var array<string, mixed> */
    protected array $queryString = [
        'filterStatus' => ['except' => 'all'],
        'filterPlan' => ['except' => 'all'],
        'sort' => ['except' => 'progress_asc'],
    ];

    /** @return Collection<int, mixed> */
    public function getTenantOnboardingData(): Collection
    {
        $tenants = Tenant::query()->latest()->get();

        $data = $tenants->map(function (Tenant $tenant) {
            $checks = $this->getOnboardingChecks($tenant);
            $completed = collect($checks)->filter(fn (bool $v) => $v)->count();

            return [
                'id' => $tenant->id,
                'name' => $tenant->store_name ?? $tenant->name,
                'subdomain' => $tenant->id,
                'owner' => $tenant->name,
                'email' => $tenant->email,
                'plan' => $tenant->plan->value ?? 'trial',
                'created_at' => $tenant->created_at,
                'days_since_signup' => $tenant->created_at ? (int) Date::parse($tenant->created_at)->diffInDays(now()) : 0,
                'checks' => $checks,
                'completed' => $completed,
                'total' => 7,
            ];
        });

        if ($this->filterStatus === 'needs_attention') {
            $data = $data->filter(fn (array $t) => $t['completed'] < 6);
        } elseif ($this->filterStatus === 'fully_onboarded') {
            $data = $data->filter(fn (array $t) => $t['completed'] === 7);
        } elseif ($this->filterStatus === 'stuck') {
            // Stuck = signed up >= 7 days ago but not fully onboarded
            $data = $data->filter(fn (array $t) => $t['days_since_signup'] >= 7 && $t['completed'] < 7);
        }

        if ($this->filterPlan !== 'all') {
            $data = $data->filter(fn (array $t) => $t['plan'] === $this->filterPlan);
        }

        $data = match ($this->sort) {
            'progress_desc' => $data->sortByDesc(fn (array $t) => [$t['completed'], $t['created_at']?->getTimestamp() ?? 0]),
            'newest' => $data->sortByDesc(fn (array $t) => $t['created_at']?->getTimestamp() ?? 0),
            'oldest' => $data->sortBy(fn (array $t) => $t['created_at']?->getTimestamp() ?? PHP_INT_MAX),
            default => $data->sortBy(fn (array $t) => [$t['completed'], -($t['created_at']?->getTimestamp() ?? 0)]),
        };

        return $data->values();
    }

    public function resetFilters(): void
    {
        $this->filterStatus = 'all';
        $this->filterPlan = 'all';
        $this->sort = 'progress_asc';
    }

    /** @return array<string, mixed> */
    protected function getOnboardingChecks(Tenant $tenant): array
    {
        $checks = [
            'store_name' => ! empty($tenant->store_name),
            'store_logo' => ! empty($tenant->store_logo),
            'storefront_enabled' => (bool) $tenant->storefront_enabled,
            'brand_customized' => ! empty($tenant->brand_color_primary) && $tenant->brand_color_primary !== BrandingSettings::DEFAULT_BRAND_COLOR,
            'has_products' => false,
            'has_categories' => false,
            'has_orders' => false,
        ];

        try {
            $tenantChecks = resolve(TenancyManager::class)->withinTenant($tenant, fn () => [
                'has_products' => DB::table('products')->count() > 0,
                'has_categories' => DB::table('categories')->count() > 0,
                'has_orders' => DB::table('orders')->count() > 0,
            ]);
            $checks = array_merge($checks, $tenantChecks);
        } catch (\Throwable) {
            // Tenant database may not be accessible
        }

        return $checks;
    }

    /** @return array<string, mixed> */
    public function getSummaryStats(): array
    {
        $data = $this->getTenantOnboardingData();

        return [
            'total' => $data->count(),
            'fully_onboarded' => $data->filter(fn (array $t) => $t['completed'] === 7)->count(),
            'needs_attention' => $data->filter(fn (array $t) => $t['completed'] < 6)->count(),
        ];
    }
}
