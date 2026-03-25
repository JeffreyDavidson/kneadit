<?php

namespace App\Filament\Pages;

use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Order;
use App\Models\Setting;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
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

        $this->deliveryOrders = Order::with(['customer', 'orderItems'])
            ->whereDate('delivery_date', $this->selectedDate)
            ->whereNotNull('delivery_address')
            ->where('delivery_address', '!=', '')
            ->orderBy('delivery_time')
            ->get()
            ->map(function (Order $order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer->name ?? 'Unknown Customer',
                    'delivery_address' => $order->delivery_address,
                    'delivery_time' => $order->delivery_time ? Date::parse($order->delivery_time)->format('H:i') : 'Not specified',
                    'total' => $order->total,
                    'distance_tier' => $this->calculateDistanceTier($order->delivery_address),
                ];
            });
    }

    private function calculateDistanceTier(string $deliveryAddress): array
    {
        // Simple distance tier calculation without external API
        // Based on postal codes, street numbers, or keywords
        $address = strtolower($deliveryAddress);

        // Simple tier system based on common patterns
        if (str_contains($address, 'downtown') ||
            str_contains($address, 'center') ||
            str_contains($address, 'main st')) {
            return ['tier' => 'Close', 'color' => 'green', 'estimated_minutes' => 10];
        } elseif (str_contains($address, 'west') ||
                  str_contains($address, 'east') ||
                  str_contains($address, 'north') ||
                  str_contains($address, 'south')) {
            return ['tier' => 'Medium', 'color' => 'yellow', 'estimated_minutes' => 20];
        } else {
            return ['tier' => 'Far', 'color' => 'red', 'estimated_minutes' => 35];
        }
    }

    public function printRoute(): void
    {
        // This will trigger a print dialog via JavaScript
        $this->dispatch('print-route');
    }

    /** @return array<string, mixed> */
    public function getRouteStats(): array
    {
        $totalOrders = $this->deliveryOrders->count();
        $totalRevenue = $this->deliveryOrders->sum('total');
        $averageDistance = $this->deliveryOrders->avg(function (array $order) {
            return $order['distance_tier']['estimated_minutes'];
        });

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'estimated_total_time' => $this->deliveryOrders->sum(function (array $order) {
                return $order['distance_tier']['estimated_minutes'];
            }),
            'average_distance_time' => round($averageDistance ?? 0, 1),
        ];
    }
}
