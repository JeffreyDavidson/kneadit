<?php

namespace App\Enums\Financial;

use App\Models\Financial\GiftCard;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum GiftCardStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Expired = 'expired';
    case Depleted = 'depleted';

    public static function resolve(GiftCard $card): self
    {
        if (! $card->is_active) {
            return self::Inactive;
        }
        if ($card->expires_at && $card->expires_at->isPast()) {
            return self::Expired;
        }
        if (! $card->current_balance->isPositive()) {
            return self::Depleted;
        }

        return self::Active;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Expired => 'danger',
            self::Depleted => 'warning',
            self::Inactive => 'gray',
        };
    }

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
