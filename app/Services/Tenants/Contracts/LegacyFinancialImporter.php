<?php

namespace App\Services\Tenants\Contracts;

interface LegacyFinancialImporter
{
    /**
     * @param array<int, array<string, mixed>> $expenses
     * @param array<int, array<string, mixed>> $incomes
     */
    public function import(array $expenses, array $incomes): void;
}
