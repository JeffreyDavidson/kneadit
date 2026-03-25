<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function salesReport(string $startDate, string $endDate): array
    {
        $start = Date::parse($startDate)->startOfDay();
        $end = Date::parse($endDate)->endOfDay();

        $orders = Order::query()->whereBetween('delivery_date', [$start, $end]);
        $paidOrders = (clone $orders)->where('payment_status', PaymentStatus::Paid);

        $totalOrders = $orders->count();
        $totalRevenue = $paidOrders->sum('total');
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $ordersByStatus = Order::query()->whereBetween('delivery_date', [$start, $end])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $topProducts = OrderItem::query()->whereHas('order', fn (Builder $q) => $q->whereBetween('delivery_date', [$start, $end])->where('payment_status', PaymentStatus::Paid))
            ->select('product_id', DB::raw('SUM(quantity) as units_sold'), DB::raw('SUM(quantity * unit_price) as revenue'))
            ->groupBy('product_id')
            ->with('product:id,name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->map(fn (OrderItem $item) => [
                'name' => $item->product?->name ?? 'Deleted Product',
                'units_sold' => (int) $item->units_sold,
                'revenue' => (float) $item->revenue,
            ])
            ->toArray();

        $revenueByDay = Order::query()->whereBetween('delivery_date', [$start, $end])
            ->where('payment_status', PaymentStatus::Paid)
            ->select(DB::raw('DATE(delivery_date) as date'), DB::raw('SUM(total) as revenue'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn (Order $row) => ['date' => $row->date, 'revenue' => (float) $row->revenue])
            ->all();

        return compact('totalOrders', 'totalRevenue', 'avgOrderValue', 'ordersByStatus', 'topProducts', 'revenueByDay');
    }

    public function customerReport(string $startDate, string $endDate): array
    {
        $start = Date::parse($startDate)->startOfDay();
        $end = Date::parse($endDate)->endOfDay();

        $newCustomers = Customer::query()->whereBetween('created_at', [$start, $end])->count();

        $totalCustomersWithOrders = Customer::query()->whereHas('orders', fn (Builder $q) => $q->whereBetween('delivery_date', [$start, $end]))->count();

        $repeatCustomers = Customer::query()->whereHas('orders', fn (Builder $q) => $q->whereBetween('delivery_date', [$start, $end]), '>=', 2)->count();

        $repeatRate = $totalCustomersWithOrders > 0 ? round(($repeatCustomers / $totalCustomersWithOrders) * 100, 1) : 0;

        $topCustomers = Customer::query()->withSum(['orders as total_spend' => fn (EloquentBuilder $q) => $q->whereBetween('delivery_date', [$start, $end])->where('payment_status', PaymentStatus::Paid)], 'total')
            ->withCount(['orders as order_count' => fn (EloquentBuilder $q) => $q->whereBetween('delivery_date', [$start, $end])])
            ->having('total_spend', '>', 0)
            ->orderByDesc('total_spend')
            ->limit(10)
            ->get()
            ->map(fn (Customer $c) => [
                'name' => $c->name,
                'email' => $c->email,
                'total_spend' => (float) $c->total_spend,
                'order_count' => (int) $c->order_count,
            ])
            ->all();

        $acquisitionByMonth = Customer::query()->whereBetween('created_at', [$start, $end])
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('COUNT(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        return compact('newCustomers', 'repeatRate', 'repeatCustomers', 'totalCustomersWithOrders', 'topCustomers', 'acquisitionByMonth');
    }

    public function productPerformanceReport(string $startDate, string $endDate): array
    {
        $start = Date::parse($startDate)->startOfDay();
        $end = Date::parse($endDate)->endOfDay();

        $products = Product::query()->withSum(['orderItems as units_sold' => fn (EloquentBuilder $q) => $q->whereHas('order', fn (EloquentBuilder $o) => $o->whereBetween('delivery_date', [$start, $end])->where('payment_status', PaymentStatus::Paid))], 'quantity')
            ->withSum(['orderItems as revenue' => fn (EloquentBuilder $q) => $q->whereHas('order', fn (EloquentBuilder $o) => $o->whereBetween('delivery_date', [$start, $end])->where('payment_status', PaymentStatus::Paid))], DB::raw('quantity * unit_price'))
            ->get()
            ->map(function (Product $p) {
                $margin = $p->price > 0 && $p->cost > 0 ? round((($p->price - $p->cost) / $p->price) * 100, 1) : null;

                return [
                    'name' => $p->name,
                    'price' => (float) $p->price,
                    'cost' => (float) $p->cost,
                    'units_sold' => (int) ($p->units_sold ?? 0),
                    'revenue' => (float) ($p->revenue ?? 0),
                    'margin' => $margin,
                ];
            })
            ->sortByDesc('revenue')
            ->values()
            ->all();

        return ['products' => $products];
    }

    public function financialSummary(int $year): array
    {
        $revenue = Order::query()->whereYear('delivery_date', $year)
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('total');

        $otherIncome = Income::query()->whereYear('date', $year)->sum('amount');
        $totalRevenue = $revenue + $otherIncome;

        $totalExpenses = Expense::query()->whereYear('date', $year)->sum('amount');
        $profit = $totalRevenue - $totalExpenses;

        $deductible = Expense::query()->whereYear('date', $year)->sum('deductible_amount');

        $monthly = collect();
        for ($m = 1; $m <= 12; $m++) {
            $mRev = Order::query()->whereYear('delivery_date', $year)->whereMonth('delivery_date', $m)->where('payment_status', PaymentStatus::Paid)->sum('total');
            $mInc = Income::query()->whereYear('date', $year)->whereMonth('date', $m)->sum('amount');
            $mExp = Expense::query()->whereYear('date', $year)->whereMonth('date', $m)->sum('amount');
            $monthly->push([
                'month' => date('M', mktime(0, 0, 0, $m, 1)),
                'revenue' => (float) ($mRev + $mInc),
                'expenses' => (float) $mExp,
                'profit' => (float) ($mRev + $mInc - $mExp),
            ]);
        }

        $expensesByCategory = Expense::query()->whereYear('date', $year)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->map(fn (string $v, string $k) => ['category' => Expense::CATEGORIES[$k] ?? ucfirst($k), 'amount' => (float) $v])
            ->values()
            ->all();

        return compact('totalRevenue', 'totalExpenses', 'profit', 'deductible', 'monthly', 'expensesByCategory');
    }

    public function inventoryReport(): array
    {
        // Pre-compute usage per ingredient in a single query (avoids N+1)
        $usageData = DB::table('recipe_ingredients')
            ->join('recipes', 'recipes.id', '=', 'recipe_ingredients.recipe_id')
            ->join('order_items', 'order_items.product_id', '=', 'recipes.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.delivery_date', '>=', now()->subDays(30))
            ->where('orders.payment_status', PaymentStatus::Paid->value)
            ->selectRaw('recipe_ingredients.ingredient_id, SUM(recipe_ingredients.quantity * order_items.quantity) as total_usage')
            ->groupBy('recipe_ingredients.ingredient_id')
            ->pluck('total_usage', 'ingredient_id');

        $ingredients = Ingredient::query()->orderBy('name')->get()->map(function (Ingredient $i) use ($usageData) {
            $usageLast30 = (float) ($usageData[$i->id] ?? 0);
            $dailyUsage = $usageLast30 / 30;
            $daysUntilStockout = $dailyUsage > 0 ? round($i->current_stock / $dailyUsage, 0) : null;

            return [
                'name' => $i->name,
                'unit' => $i->unit,
                'current_stock' => (float) $i->current_stock,
                'low_stock_threshold' => (float) $i->low_stock_threshold,
                'is_low' => $i->current_stock <= $i->low_stock_threshold,
                'is_out' => $i->current_stock <= 0,
                'daily_usage' => round($dailyUsage, 2),
                'days_until_stockout' => $daysUntilStockout,
                'cost_per_unit' => (float) $i->cost_per_unit,
            ];
        })->all();

        $totalItems = count($ingredients);
        $lowStockItems = collect($ingredients)->where('is_low', true)->count();
        $outOfStockItems = collect($ingredients)->where('is_out', true)->count();

        return compact('ingredients', 'totalItems', 'lowStockItems', 'outOfStockItems');
    }
}
