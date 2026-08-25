<?php

namespace App\Filament\Pages\Operations;

use App\Enums\Orders\OrderStatus;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Customers\Customer;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Laravel\Pennant\Feature;
use Livewire\Attributes\Url;

class ReorderReminders extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    protected string $view = 'filament.pages.operations.reorder-reminders';

    protected static ?string $title = 'Reorder Reminders';

    protected static ?string $navigationLabel = 'Reorder Reminders';

    protected static ?int $navigationSort = 2;

    #[Url]
    public int $threshold = 60;

    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::OutlinedBellAlert;
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Communication';
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/admin' => 'Dashboard',
            '' => 'Reorder Reminders',
        ];
    }

    /** @return Collection<int, Customer> */
    public function getCustomers(): Collection
    {
        $cutoff = Date::now()->subDays($this->threshold);
        $eligibleOrders = fn (Builder $query): Builder => $query
            ->where('status', '!=', OrderStatus::Cancelled)
            ->whereNotNull('delivery_date');

        return Customer::query()
            ->select([
                'customers.id',
                'customers.email as customer_email',
                'customers.name as customer_name',
            ])
            ->withMax(['orders as last_order_date' => $eligibleOrders], 'delivery_date')
            ->withCount(['orders as total_orders' => $eligibleOrders])
            ->withSum(['orders as total_spent' => $eligibleOrders], 'total')
            ->whereHas('orders', $eligibleOrders)
            ->whereDoesntHave('orders', fn (Builder $query): Builder => $eligibleOrders($query)
                ->where('delivery_date', '>', $cutoff))
            ->orderBy('last_order_date')
            ->get()
            ->map(function (Customer $customer): Customer {
                $customer->days_since = (int) floor(Date::parse($customer->last_order_date)->diffInDays(now()));

                return $customer;
            });
    }

    public function updatedThreshold(): void
    {
        // Livewire will re-render automatically
    }
}
