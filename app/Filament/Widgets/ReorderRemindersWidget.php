<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Customer;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class ReorderRemindersWidget extends Widget
{
    protected static ?int $sort = 20;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.reorder-reminders-widget';

    /** @return array<int, array<string, string>> */
    public function getLapsedCustomers(): array
    {
        $thirtyDaysAgo = Date::now()->subDays(30);

        return Customer::query()->select('customers.id', 'customers.name', 'customers.email')
            ->join('orders', 'orders.customer_id', '=', 'customers.id')
            ->whereNotIn('orders.status', [OrderStatus::Cancelled->value])
            ->selectRaw('MAX(orders.created_at) as last_order_at')
            ->groupBy('customers.id', 'customers.name', 'customers.email')
            ->havingRaw('COUNT(orders.id) >= 2')
            ->havingRaw('MAX(orders.created_at) < ?', [$thirtyDaysAgo])
            ->orderByRaw('MAX(orders.created_at) DESC')
            ->limit(10)
            ->get()
            ->map(fn (Customer $c) => [
                'name' => $c->name,
                'email' => $c->email,
                'last_order' => $c->last_order_at ? Carbon::parse($c->last_order_at)->diffForHumans() : 'N/A',
            ])
            ->all();
    }

    public function getLapsedCount(): int
    {
        $thirtyDaysAgo = Date::now()->subDays(30);

        return (int) DB::table(
            Customer::query()->join('orders', 'orders.customer_id', '=', 'customers.id')
                ->whereNotIn('orders.status', [OrderStatus::Cancelled->value])
                ->groupBy('customers.id')
                ->havingRaw('COUNT(orders.id) >= 2')
                ->havingRaw('MAX(orders.created_at) < ?', [$thirtyDaysAgo])
                ->select('customers.id')->toBase(),
            'lapsed'
        )->count();
    }
}
