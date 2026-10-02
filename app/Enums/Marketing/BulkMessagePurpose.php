<?php

declare(strict_types=1);

namespace App\Enums\Marketing;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

/**
 * Why a baker is sending a bulk message to customers. The purpose decides
 * who may receive it: an order update is transactional and ignores the
 * marketing opt-out, a promotion is marketing and respects it.
 */
enum BulkMessagePurpose: string implements HasDescription, HasLabel
{
    case OrderUpdate = 'order_update';
    case Promotion = 'promotion';

    public function getLabel(): string
    {
        return match ($this) {
            self::OrderUpdate => 'Order update',
            self::Promotion => 'Promotion',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::OrderUpdate => 'Only for customers with an open order. Sent even if they unsubscribed from marketing.',
            self::Promotion => 'Sent only to customers subscribed to marketing; includes an unsubscribe link.',
        };
    }

    /** Why a selected customer is left out of a send with this purpose. */
    public function skipReason(): string
    {
        return match ($this) {
            self::OrderUpdate => 'no open order',
            self::Promotion => 'unsubscribed',
        };
    }
}
