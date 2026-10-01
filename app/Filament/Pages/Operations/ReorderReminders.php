<?php

namespace App\Filament\Pages\Operations;

use App\Enums\Orders\OrderStatus;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Customers\Customer;
use App\Services\Scheduling\BakeryClock;
use App\Services\Settings\TenantSettings;
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

    #[\Override]
    protected string $view = 'filament.pages.operations.reorder-reminders';

    #[\Override]
    protected static ?string $title = 'Reorder Reminders';

    #[\Override]
    protected static ?string $navigationLabel = 'Reorder Reminders';

    #[\Override]
    protected static ?int $navigationSort = 2;

    #[Url]
    public int $threshold = 60;

    #[\Override]
    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    #[\Override]
    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::OutlinedBellAlert;
    }

    #[\Override]
    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Communication';
    }

    #[\Override]
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
        $today = resolve(BakeryClock::class)->today();
        $cutoff = $today->copy()->subDays($this->threshold);
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
            ->map(function (Customer $customer) use ($today): Customer {
                $customer->days_since = (int) floor(Date::parse($customer->last_order_date)->startOfDay()->diffInDays($today));

                return $customer;
            });
    }

    public function reminderMailto(Customer $customer): string
    {
        $storeName = resolve(TenantSettings::class)->store->name;
        $subject = rawurlencode("We miss you at {$storeName}!");
        $body = rawurlencode("Hi {$customer->customer_name},\n\nIt's been a while since your last visit and we miss you! We've been baking up some amazing new treats and would love to see you again.\n\nVisit us to place your next order.\n\nWarmly,\n{$storeName}");

        return "mailto:{$customer->customer_email}?subject={$subject}&body={$body}";
    }

    public function updatedThreshold(): void
    {
        // Livewire will re-render automatically
    }
}
