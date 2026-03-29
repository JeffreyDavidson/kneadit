<?php

namespace App\Reports;

use App\Enums\ExpenseCategory;
use App\Enums\PaymentStatus;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class FinancialReport
{
    /** @return array<string, mixed> */
    public function generate(int $year): array
    {
        $revenue = Order::query()->whereYear('delivery_date', $year)
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('total');

        $otherIncome = Income::query()->whereYear('date', $year)->sum('amount');
        $totalRevenue = $revenue + $otherIncome;

        $totalExpenses = Expense::query()->whereYear('date', $year)->sum('amount');
        $profit = $totalRevenue - $totalExpenses;

        $deductible = Expense::query()->whereYear('date', $year)->sum('deductible_amount');

        $monthlyRevenue = Order::query()->whereYear('delivery_date', $year)
            ->where('payment_status', PaymentStatus::Paid)
            ->selectRaw("strftime('%m', delivery_date) as m, SUM(total) as total")
            ->groupBy('m')
            ->pluck('total', 'm');

        $monthlyIncome = Income::query()->whereYear('date', $year)
            ->selectRaw("strftime('%m', date) as m, SUM(amount) as total")
            ->groupBy('m')
            ->pluck('total', 'm');

        $monthlyExpenses = Expense::query()->whereYear('date', $year)
            ->selectRaw("strftime('%m', date) as m, SUM(amount) as total")
            ->groupBy('m')
            ->pluck('total', 'm');

        $monthly = collect();
        for ($m = 1; $m <= 12; $m++) {
            $mRev = (float) ($monthlyRevenue[$m] ?? 0);
            $mInc = (float) ($monthlyIncome[$m] ?? 0);
            $mExp = (float) ($monthlyExpenses[$m] ?? 0);
            $monthly->push([
                'month' => date('M', (int) mktime(0, 0, 0, $m, 1)),
                'revenue' => $mRev + $mInc,
                'expenses' => $mExp,
                'profit' => $mRev + $mInc - $mExp,
            ]);
        }

        $expensesByCategory = Expense::query()->whereYear('date', $year)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->map(fn (string $v, string $k) => ['category' => ExpenseCategory::tryFrom($k)?->getLabel() ?? ucfirst($k), 'amount' => (float) $v])
            ->values()
            ->all();

        return compact('totalRevenue', 'totalExpenses', 'profit', 'deductible', 'monthly', 'expensesByCategory');
    }
}
