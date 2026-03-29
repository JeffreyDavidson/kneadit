<?php

namespace App\Filament\Central\Pages;

use App\Models\Tenant;
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

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Onboarding Tracker';

    protected string $view = 'filament.central.pages.onboarding-tracker';

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
                'plan' => $tenant->plan ?? 'free',
                'created_at' => $tenant->created_at,
                'days_since_signup' => $tenant->created_at ? (int) Date::parse($tenant->created_at)->diffInDays(now()) : 0,
                'checks' => $checks,
                'completed' => $completed,
                'total' => 7,
            ];
        });

        return $data->sortBy(fn (array $t) => [$t['completed'], $t['created_at']])->values();
    }

    /** @return array<string, mixed> */
    protected function getOnboardingChecks(Tenant $tenant): array
    {
        $checks = [
            'store_name' => ! empty($tenant->store_name),
            'store_logo' => ! empty($tenant->store_logo),
            'storefront_enabled' => (bool) $tenant->storefront_enabled,
            'brand_customized' => ! empty($tenant->brand_color_primary) && $tenant->brand_color_primary !== '#d4920c',
            'has_products' => false,
            'has_categories' => false,
            'has_orders' => false,
        ];

        try {
            tenancy()->initialize($tenant);

            $checks['has_products'] = DB::table('products')->count() > 0;
            $checks['has_categories'] = DB::table('categories')->count() > 0;
            $checks['has_orders'] = DB::table('orders')->count() > 0;

            tenancy()->end();
        } catch (\Throwable $e) {
            tenancy()->end();
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
