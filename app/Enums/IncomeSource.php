<?php

namespace App\Enums;

enum IncomeSource: string
{
    case FarmersMarket = 'farmers_market';
    case CashSale = 'cash_sale';
    case PaypalDirect = 'paypal_direct';
    case Catering = 'catering';
    case Other = 'other';

    public function label(): string
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
