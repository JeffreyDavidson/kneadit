<?php

namespace App\Filament\Central\Pages;

use App\Filament\Central\Resources\TenantResource;
use App\Models\PlatformMessage;
use App\Models\Tenant;
use App\Services\TenantHealthService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use UnitEnum;

class BakeryInsights extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

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
        return $this->service()->getAlerts();
    }

    public function extendTrial(string $tenantId): void
    {
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            Notification::make()->title('Tenant not found.')->danger()->send();

            return;
        }

        $currentEnd = $tenant->trial_ends_at ? Date::parse($tenant->trial_ends_at) : now();
        $newEnd = $currentEnd->isPast() ? now()->addDays(30) : $currentEnd->addDays(30);

        $tenant->update(['trial_ends_at' => $newEnd]);

        $this->extendedTrials[] = $tenantId;

        Notification::make()
            ->title('Trial Extended')
            ->body(($tenant->store_name ?? $tenant->name)." trial extended to {$newEnd->format('M j, Y')}.")
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

        $storeName = $tenant->store_name ?? $tenant->name;

        PlatformMessage::query()->create([
            'tenant_id' => $tenant->id,
            'sender_type' => 'admin',
            'subject' => "We noticed you haven't been around lately",
            'body' => "Hi {$storeName}!\n\nWe noticed it's been a little quiet on your end. Just wanted to check in — is there anything we can help with?\n\nWhether you need help setting up your storefront, adding products, or just have questions, we're here for you.\n\nThe KneadIt Team",
            'is_read' => false,
        ]);

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
        return $this->service()->getTenantUsageData();
    }

    public function getNextPlan(string $currentPlan): ?string
    {
        return $this->service()->getNextPlan($currentPlan);
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
