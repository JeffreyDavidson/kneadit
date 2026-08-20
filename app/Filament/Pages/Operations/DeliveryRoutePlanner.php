<?php

namespace App\Filament\Pages\Operations;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Delivery\DeliveryRouteService;
use App\Services\Settings\TenantSettings;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Laravel\Pennant\Feature;

/**
 * @phpstan-type DistanceTier array{tier: string, color: string, estimated_minutes: int}
 * @phpstan-type DeliveryOrder array{id: int, order_number: string, customer_name: string, delivery_address: ?string, delivery_time: non-falsy-string, total: float, distance_tier: DistanceTier}
 */
class DeliveryRoutePlanner extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $navigationLabel = 'Delivery Planner';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.operations.delivery-route-planner';

    public ?string $selectedDate = null;

    /** @var Collection<int, DeliveryOrder> */
    public Collection $deliveryOrders;

    public ?string $storeAddress = null;

    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->storeAddress = resolve(TenantSettings::class)->store->address ?? 'Store address not configured';
        $this->loadOrders();
    }

    public function updatedSelectedDate(): void
    {
        $this->loadOrders();
    }

    public function loadOrders(): void
    {
        if (! $this->selectedDate) {
            $this->deliveryOrders = new Collection;

            return;
        }

        $this->deliveryOrders = resolve(DeliveryRouteService::class)->loadOrders($this->selectedDate);
    }

    public function printRoute(): void
    {
        $this->dispatch('print-route');
    }

    /** @return array<string, mixed> */
    public function getRouteStats(): array
    {
        return resolve(DeliveryRouteService::class)->getRouteStats($this->deliveryOrders);
    }
}
