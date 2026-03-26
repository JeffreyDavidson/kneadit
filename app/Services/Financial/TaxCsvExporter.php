<?php

namespace App\Services\Financial;

use App\Enums\PaymentStatus;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Order;
use App\Models\OrderItem;
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
                        $order->subtotal,
                        $order->delivery_fee,
                        $order->discount_amount,
                        $order->total,
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
                        $expense->amount,
                        $expense->business_percentage,
                        $expense->deductible_amount,
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
                        $income->source_label,
                        $income->description,
                        $income->amount,
                        'Gross Receipts (Schedule C Line 1)',
                    ]);
                }
            });
    }

    /** @param resource $handle */
    public function writeSummaryCsv(mixed $handle, string $from, string $to): void
    {
        $totalOrderRevenue = Order::query()->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('total');

        $totalIncomeRevenue = Income::query()->whereBetween('date', [$from, $to])->sum('amount');
        $totalRevenue = (float) $totalOrderRevenue + (float) $totalIncomeRevenue;

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
