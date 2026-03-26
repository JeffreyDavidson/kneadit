<?php

namespace App\Enums;

enum TaxExportType: string
{
    case All = 'all';
    case Orders = 'orders';
    case Expenses = 'expenses';
    case Income = 'income';
    case Summary = 'summary';

    public function label(): string
    {
        return match ($this) {
            self::All => 'All (Orders + Expenses + Income + Summary)',
            self::Orders => 'Orders Only',
            self::Expenses => 'Expenses Only',
            self::Income => 'Income Only',
            self::Summary => 'Summary Only',
        };
    }
}
