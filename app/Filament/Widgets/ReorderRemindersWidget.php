<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Customer;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class ReorderRemindersWidget extends Widget
{
    protected static ?int $sort = 20;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.reorder-reminders-widget';

    public function getLapsedCustomers(): array
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        return Customer::select('customers.id', 'customers.name', 'customers.email')
            ->join('orders', 'orders.customer_id', '=', 'customers.id')
            ->whereNotIn('orders.status', [OrderStatus::Cancelled->value])
            ->groupBy('customers.id', 'customers.name', 'customers.email')
            ->havingRaw('COUNT(orders.id) >= 2')
            ->havingRaw('MAX(orders.created_at) < ?', [$thirtyDaysAgo])
            ->orderByRaw('MAX(orders.created_at) DESC')
            ->limit(10)
            ->get()
            ->map(fn ($c) => [
                'name' => $c->name,
                'email' => $c->email,
                'last_order' => $c->orders()->latest()->value('created_at')?->diffForHumans() ?? 'N/A',
            ])
            ->toArray();
    }

    public function getLapsedCount(): int
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        return Customer::join('orders', 'orders.customer_id', '=', 'customers.id')
            ->whereNotIn('orders.status', [OrderStatus::Cancelled->value])
            ->groupBy('customers.id')
            ->havingRaw('COUNT(orders.id) >= 2')
            ->havingRaw('MAX(orders.created_at) < ?', [$thirtyDaysAgo])
            ->get()
            ->count();
    }
}
