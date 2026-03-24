<?php

namespace App\Enums;

enum LoyaltyPointType: string
{
    case Earned = 'earned';
    case Redeemed = 'redeemed';
    case Adjusted = 'adjusted';
}
