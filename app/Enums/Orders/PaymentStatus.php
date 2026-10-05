<?php

namespace App\Enums\Orders;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasLabel
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    /** Whether money is still owed, so the order can be marked paid. */
    public function canBeMarkedPaid(): bool
    {
        return match ($this) {
            self::Unpaid, self::Partial => true,
            self::Paid, self::Cancelled, self::Refunded => false,
        };
    }

    /** Whether customer money is currently held for the order, in full or as a deposit. */
    public function holdsPayment(): bool
    {
        return match ($this) {
            self::Partial, self::Paid => true,
            self::Unpaid, self::Cancelled, self::Refunded => false,
        };
    }

    /** Whether an order with this payment status may still earn loyalty points. */
    public function earnsLoyaltyPoints(): bool
    {
        return match ($this) {
            self::Unpaid, self::Partial, self::Paid => true,
            self::Cancelled, self::Refunded => false,
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Unpaid => 'danger',
            self::Partial => 'warning',
            self::Paid => 'success',
            self::Cancelled => 'gray',
            self::Refunded => 'warning',
        };
    }

    /**
     * Whether an order with this payment status may be hard-deleted: only when
     * no money was taken (Unpaid) or the payment was voided (Cancelled).
     */
    public function allowsDeletion(): bool
    {
        return match ($this) {
            self::Unpaid, self::Cancelled => true,
            self::Partial, self::Paid, self::Refunded => false,
        };
    }
}
