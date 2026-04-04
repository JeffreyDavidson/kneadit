<?php

namespace App\Filament\Central\Pages;

use App\Actions\Tenants\ExtendTenantTrial;
use App\Actions\Tenants\SendTenantNudge;
use App\Filament\Central\Resources\TenantResource;
use App\Models\Platform\Tenant;
use App\Services\Tenants\ChurnAlertService;
use App\Services\Tenants\TenantHealthService;
use App\Services\Tenants\TenantUsageService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class BakeryInsights extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Bakery Insights';

    protected string $view = 'filament.central.pages.bakery-insights';

    public string $activeTab = 'health';

    /** @var array<int|string, mixed> */
    public array $extendedTrials = [];

    /** @var array<int|string, mixed> */
    public array $sentNudges = [];

    private function service(): TenantHealthService
    {
        return resolve(TenantHealthService::class);
    }

    // ── Health Tab Methods ──

    /** @return Collection<int, mixed> */
    public function getTenantHealthData(): Collection
    {
        return $this->service()->getTenantHealthData();
    }

    /** @return array<string, mixed> */
    public function getHealthSummaryStats(): array
    {
        return $this->service()->getHealthSummaryStats();
    }

    // ── Churn Alerts Tab Methods ──

    /** @return Collection<int, mixed> */
    public function getAlerts(): Collection
    {
        return resolve(ChurnAlertService::class)->getAlerts();
    }

    public function extendTrial(string $tenantId): void
    {
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            Notification::make()->title('Tenant not found.')->danger()->send();

            return;
        }

        $newEnd = resolve(ExtendTenantTrial::class)($tenant);

        $this->extendedTrials[] = $tenantId;

        Notification::make()
            ->title('Trial Extended')
            ->body(($tenant->store_name ?? $tenant->name) . " trial extended to {$newEnd->format('M j, Y')}.")
            ->success()
            ->send();
    }

    public function sendNudge(string $tenantId): void
    {
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            Notification::make()->title('Tenant not found.')->danger()->send();

            return;
        }

        resolve(SendTenantNudge::class)($tenant);

        $storeName = $tenant->store_name ?? $tenant->name;
        $this->sentNudges[] = $tenantId;

        Notification::make()
            ->title('Nudge Sent')
            ->body("A check-in message has been sent to {$storeName}.")
            ->success()
            ->send();
    }

    public function getViewTenantUrl(string $tenantId): string
    {
        return TenantResource::getUrl('view', ['record' => $tenantId]);
    }

    // ── Upgrade Triggers Tab Methods ──

    /** @return Collection<int, mixed> */
    public function getTenantUsageData(): Collection
    {
        return resolve(TenantUsageService::class)->getTenantUsageData();
    }

    public function getNextPlan(string $currentPlan): ?string
    {
        return resolve(TenantUsageService::class)->getNextPlan($currentPlan)?->getLabel();
    }

    public function suggestUpgrade(string $tenantId): void
    {
        Notification::make()
            ->title('Upgrade suggestion noted')
            ->body("Upgrade suggestion for tenant {$tenantId} has been queued.")
            ->success()
            ->send();
    }
}
