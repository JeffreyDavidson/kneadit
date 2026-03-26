<?php

namespace App\Enums;

enum StockStatus: string
{
    case Good = 'good';
    case Low = 'low';
    case Out = 'out';
}
