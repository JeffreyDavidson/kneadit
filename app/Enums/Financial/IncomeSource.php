<?php

namespace App\Enums\Financial;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum IncomeSource: string implements HasColor, HasLabel
{
    case FarmersMarket = 'farmers_market';
    case CashSale = 'cash_sale';
    case PaypalDirect = 'paypal_direct';
    case Catering = 'catering';
    case Other = 'other';

    public function getColor(): string
    {
        return match ($this) {
            self::FarmersMarket => 'success',
            self::CashSale => 'primary',
            self::PaypalDirect => 'info',
            self::Catering => 'warning',
            self::Other => 'gray',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::FarmersMarket => 'Farmers Market',
            self::CashSale => 'Cash Sale',
            self::PaypalDirect => 'PayPal Direct',
            self::Catering => 'Catering',
            self::Other => 'Other',
        };
    }
}
