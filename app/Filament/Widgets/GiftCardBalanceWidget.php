<?php

namespace App\Filament\Widgets;

use App\Models\GiftCard;
use App\Models\GiftCardTransaction;
use Filament\Widgets\Widget;

class GiftCardBalanceWidget extends Widget
{
    protected static ?int $sort = 16;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.gift-card-balance-widget';

    public function getTotalOutstandingBalance(): float
    {
        return (float) GiftCard::where('is_active', true)->sum('current_balance');
    }

    public function getActiveCardsCount(): int
    {
        return GiftCard::where('is_active', true)
            ->where('current_balance', '>', 0)
            ->count();
    }

    public function getRecentlyRedeemed(): array
    {
        return GiftCardTransaction::with('giftCard')->latest()
            ->limit(3)
            ->get()
            ->map(fn (GiftCardTransaction $t) => [
                'code' => $t->giftCard?->code ?? 'N/A',
                'amount' => $t->amount ?? 0,
                'date' => $t->created_at?->diffForHumans() ?? '',
            ])
            ->all();
    }
}
