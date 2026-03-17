<?php

namespace App\Enums;

enum GiftCardStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Expired = 'expired';
    case Depleted = 'depleted';
}
