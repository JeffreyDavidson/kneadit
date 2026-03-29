<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TaxExportType: string implements HasLabel
{
    case All = 'all';
    case Orders = 'orders';
    case Expenses = 'expenses';
    case Income = 'income';
    case Summary = 'summary';

    public function getLabel(): string
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
