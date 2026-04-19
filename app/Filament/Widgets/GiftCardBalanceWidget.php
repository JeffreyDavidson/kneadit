<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;
use Filament\Widgets\Widget;

class GiftCardBalanceWidget extends Widget
{
    use CachesWidgetData;

    protected static ?int $sort = 16;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.gift-card-balance-widget';

    public function getTotalOutstandingBalance(): float
    {
        return $this->cached('outstanding_balance', [300, 600], fn (): float => (float) GiftCard::query()->where('is_active', true)->sum('current_balance'));
    }

    public function getActiveCardsCount(): int
    {
        return $this->cached('active_count', [300, 600], fn (): int => GiftCard::query()->where('is_active', true)
            ->where('current_balance', '>', 0)
            ->count());
    }

    /** @return array<int, array<string, mixed>> */
    public function getRecentlyRedeemed(): array
    {
        return $this->cached('recent_redeemed', [300, 600], fn (): array => GiftCardTransaction::with('giftCard')->latest()
            ->limit(3)
            ->get()
            ->map(fn (GiftCardTransaction $t) => [
                'code' => $t->giftCard->code ?? 'N/A',
                'amount' => $t->amount ?? 0,
                'date' => $t->created_at?->diffForHumans(),
            ])
            ->all());
    }

    protected function cachePrefix(): string
    {
        return 'gift_card_balance';
    }
}
