<?php

namespace App\Services\Financial;

use App\Enums\Orders\PaymentStatus;
use App\Models\Financial\Expense;
use App\Models\Financial\Income;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use Illuminate\Database\Eloquent\Collection;

class TaxCsvExporter
{
    /** @param resource $handle */
    public function writeOrdersCsv(mixed $handle, string $from, string $to): void
    {
        fputcsv($handle, ['=== ORDERS ===']);
        fputcsv($handle, ['Date', 'Order Number', 'Customer', 'Items', 'Subtotal', 'Delivery Fee', 'Discount', 'Total', 'Payment Status', 'Payment Method']);

        Order::with(['customer', 'orderItems.product'])
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])->oldest()
            ->chunk(100, function (Collection $orders) use ($handle) {
                foreach ($orders as $order) {
                    $items = $order->orderItems->map(fn (OrderItem $i) => ($i->product->name ?? 'Item') . ' x' . $i->quantity)->implode('; ');
                    fputcsv($handle, [
                        $order->created_at?->format('Y-m-d'),
                        $order->order_number,
                        $order->customer->name ?? 'N/A',
                        $items,
                        number_format($order->subtotal->dollars(), 2, '.', ''),
                        number_format($order->delivery_fee->dollars(), 2, '.', ''),
                        number_format($order->discount_amount->dollars(), 2, '.', ''),
                        number_format($order->total->dollars(), 2, '.', ''),
                        $order->payment_status->value,
                        $order->payment_method->value,
                    ]);
                }
            });
    }

    /** @param resource $handle */
    public function writeExpensesCsv(mixed $handle, string $from, string $to): void
    {
        fputcsv($handle, ['=== EXPENSES ===']);
        fputcsv($handle, ['Date', 'Category (IRS Schedule C)', 'Description', 'Amount', 'Business Use %', 'Deductible Amount', 'Vendor']);

        $categoryMap = [
            'ingredients' => 'Cost of Goods Sold (Line 4)',
            'supplies' => 'Supplies (Line 22)',
            'packaging' => 'Supplies (Line 22)',
            'booth_fees' => 'Other Expenses (Line 27a)',
            'delivery' => 'Car & Truck Expenses (Line 9)',
            'marketing' => 'Advertising (Line 8)',
            'insurance' => 'Insurance (Line 15)',
            'education' => 'Other Expenses (Line 27a)',
            'equipment' => 'Depreciation (Line 13)',
            'other' => 'Other Expenses (Line 27a)',
        ];

        Expense::query()->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->chunk(100, function (Collection $expenses) use ($handle, $categoryMap) {
                foreach ($expenses as $expense) {
                    fputcsv($handle, [
                        $expense->date?->format('Y-m-d'),
                        $categoryMap[$expense->category->value],
                        $expense->description,
                        $expense->amount->dollars(),
                        $expense->business_percentage->value(),
                        $expense->deductible_amount->dollars(),
                        $expense->notes ?? '',
                    ]);
                }
            });
    }

    /** @param resource $handle */
    public function writeIncomeCsv(mixed $handle, string $from, string $to): void
    {
        fputcsv($handle, ['=== INCOME ===']);
        fputcsv($handle, ['Date', 'Source', 'Description', 'Amount', 'Category']);

        Income::query()->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->chunk(100, function (Collection $incomes) use ($handle) {
                foreach ($incomes as $income) {
                    fputcsv($handle, [
                        $income->date?->format('Y-m-d'),
                        $income->source->getLabel(),
                        $income->description,
                        $income->amount->dollars(),
                        'Gross Receipts (Schedule C Line 1)',
                    ]);
                }
            });
    }

    /** @param resource $handle */
    public function writeSummaryCsv(mixed $handle, string $from, string $to): void
    {
        // orders.total is bigint cents (migration 2026_04_22_201500); incomes.amount
        // is still decimal dollars (separate table, not yet migrated).
        $totalOrderRevenue = (int) Order::query()->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('total') / 100;

        $totalIncomeRevenue = Income::query()->whereBetween('date', [$from, $to])->sum('amount');
        $totalRevenue = $totalOrderRevenue + (float) $totalIncomeRevenue;

        $totalExpenses = (float) Expense::query()->whereBetween('date', [$from, $to])->sum('amount');
        $totalDeductible = (float) Expense::query()->whereBetween('date', [$from, $to])->sum('deductible_amount');
        $netProfit = $totalRevenue - $totalDeductible;

        fputcsv($handle, ['=== TAX SUMMARY ===']);
        fputcsv($handle, ['Metric', 'Amount']);
        fputcsv($handle, ['Total Revenue (Orders)', number_format((float) $totalOrderRevenue, 2)]);
        fputcsv($handle, ['Total Revenue (Other Income)', number_format((float) $totalIncomeRevenue, 2)]);
        fputcsv($handle, ['Total Revenue (Combined)', number_format($totalRevenue, 2)]);
        fputcsv($handle, ['Total Expenses', number_format($totalExpenses, 2)]);
        fputcsv($handle, ['Total Deductible', number_format($totalDeductible, 2)]);
        fputcsv($handle, ['Net Profit (Revenue - Deductible)', number_format($netProfit, 2)]);
    }
}
