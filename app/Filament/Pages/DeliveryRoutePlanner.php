<?php

namespace App\Filament\Pages;

use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Setting;
use App\Services\DeliveryRouteService;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Pennant\Feature;

class DeliveryRoutePlanner extends Page
{
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Delivery Planner';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.delivery-route-planner';

    public ?string $selectedDate = null;

    /** @var Collection<int, mixed> */
    public Collection $deliveryOrders;

    public ?string $storeAddress = null;

    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->storeAddress = Setting::get('store_address') ?? 'Store address not configured';
        $this->loadOrders();
    }

    public function updatedSelectedDate(): void
    {
        $this->loadOrders();
    }

    public function loadOrders(): void
    {
        if (! $this->selectedDate) {
            $this->deliveryOrders = collect();

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
