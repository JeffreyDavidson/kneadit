<?php

namespace App\Filament\Pages\Operations;

use App\Enums\Orders\OrderStatus;
use App\Enums\Platform\SubscriptionTier;
use App\Enums\Staff\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Orders\Order;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Laravel\Pennant\Feature;
use Livewire\Attributes\Url;

class ReorderReminders extends Page
{
    use ShowsUpgradeBadge;

    protected string $view = 'filament.pages.operations.reorder-reminders';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    protected static ?string $title = 'Reorder Reminders';

    protected static ?string $navigationLabel = 'Reorder Reminders';

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::OutlinedBellAlert;
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Communication';
    }

    #[Url]
    public int $threshold = 60;

    public function getBreadcrumbs(): array
    {
        return [
            '/admin' => 'Dashboard',
            '' => 'Reorder Reminders',
        ];
    }

    /** @return Collection<int, mixed> */
    public function getCustomers(): Collection
    {
        $cutoff = Date::now()->subDays($this->threshold);

        return Order::query()
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->select([
                'customers.email as customer_email',
                DB::raw('MAX(customers.name) as customer_name'),
                DB::raw('MAX(orders.delivery_date) as last_order_date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(orders.total) as total_spent'),
            ])
            ->where('orders.status', '!=', OrderStatus::Cancelled)
            ->groupBy('customers.email')
            ->havingRaw('MAX(orders.delivery_date) <= ?', [$cutoff->toDateString()])
            ->orderBy(DB::raw('MAX(orders.delivery_date)'), 'asc')
            ->get()
            ->map(function ($customer) {
                $customer->days_since = (int) floor(Date::parse($customer->last_order_date)->diffInDays(now()));

                return $customer;
            });
    }

    public function updatedThreshold(): void
    {
        // Livewire will re-render automatically
    }
}
