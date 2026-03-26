<?php

namespace App\Enums;

enum TaxExportType: string
{
    case All = 'all';
    case Orders = 'orders';
    case Expenses = 'expenses';
    case Income = 'income';
    case Summary = 'summary';
}
