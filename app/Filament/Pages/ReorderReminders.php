<?php

namespace App\Filament\Pages;

use App\Filament\Traits\RequiresRole;
use App\Models\Order;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;

class ReorderReminders extends Page
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static string $requiredPlan = 'growth';

    protected string $view = 'filament.pages.reorder-reminders';

    protected static ?string $title = 'Reorder Reminders';

    protected static ?string $navigationLabel = 'Reorder Reminders';

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-bell-alert';
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

    public function getCustomers(): \Illuminate\Support\Collection
    {
        $cutoff = Carbon::now()->subDays($this->threshold);

        return Order::query()
            ->select([
                'customer_email',
                DB::raw('MAX(customer_name) as customer_name'),
                DB::raw('MAX(requested_date) as last_order_date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total) as total_spent'),
            ])
            ->where('status', '!=', 'cancelled')
            ->groupBy('customer_email')
            ->having(DB::raw('MAX(requested_date)'), '<=', $cutoff->toDateString())
            ->orderBy(DB::raw('MAX(requested_date)'), 'asc')
            ->get()
            ->map(function ($customer) {
                $customer->days_since = (int) floor(Carbon::parse($customer->last_order_date)->diffInDays(now()));

                return $customer;
            });
    }

    public function updatedThreshold(): void
    {
        // Livewire will re-render automatically
    }
}
