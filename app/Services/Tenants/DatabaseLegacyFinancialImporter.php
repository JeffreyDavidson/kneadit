<?php

namespace App\Services\Tenants;

use App\Contracts\Tenants\LegacyFinancialImporter;
use Illuminate\Support\Facades\DB;
use UnexpectedValueException;

class DatabaseLegacyFinancialImporter implements LegacyFinancialImporter
{
    /**
     * @param array<int, array<string, mixed>> $expenses
     * @param array<int, array<string, mixed>> $incomes
     */
    public function import(array $expenses, array $incomes): void
    {
        foreach ($expenses as $expense) {
            $businessPercentage = $this->integer($expense['business_percentage'] ?? 100);
            DB::table('expenses')->updateOrInsert(
                ['description' => $expense['description'], 'date' => $expense['date']],
                ['amount' => $this->cents($expense['amount']), 'category' => $expense['category'] === 'delivery_gas' ? 'delivery' : $expense['category'], 'receipt_image' => $expense['receipt'] ?? null, 'notes' => $expense['notes'] ?? null, 'business_percentage' => $businessPercentage, 'deductible_amount' => $this->cents($this->number($expense['amount']) * $businessPercentage / 100), 'created_at' => $expense['created_at'] ?? now(), 'updated_at' => $expense['updated_at'] ?? now()],
            );
        }

        foreach ($incomes as $income) {
            $source = in_array($income['source'], ['farmers_market', 'cash_sale', 'paypal_direct', 'catering'], true) ? $income['source'] : 'other';
            DB::table('incomes')->updateOrInsert(
                ['description' => $income['description'], 'date' => $income['date']],
                ['amount' => $this->cents($income['amount']), 'source' => $source, 'notes' => $income['notes'] ?? null, 'created_at' => $income['created_at'] ?? now(), 'updated_at' => $income['updated_at'] ?? now()],
            );
        }
    }

    private function integer(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new UnexpectedValueException('Expected an integer-compatible legacy value.');
        }

        return (int) $value;
    }

    private function number(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }
        throw new UnexpectedValueException('Expected a numeric legacy value.');
    }

    private function cents(mixed $value): int
    {
        return (int) round($this->number($value) * 100);
    }
}
