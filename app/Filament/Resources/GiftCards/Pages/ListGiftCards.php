<?php

declare(strict_types=1);

namespace App\Filament\Resources\GiftCards\Pages;

use App\Actions\GiftCards\CreateGiftCard;
use App\DataTransferObjects\GiftCards\CreateGiftCardData;
use App\Filament\Resources\GiftCards\GiftCardResource;
use App\Models\Financial\GiftCard;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGiftCards extends ListRecords
{
    #[\Override]
    protected static string $resource = GiftCardResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver()
                ->using($this->createGiftCard(...)),
        ];
    }

    /**
     * Create through the same action as an online purchase so the card gets
     * its code and its Purchase ledger row.
     *
     * @param  array<string, mixed>  $data
     */
    private function createGiftCard(array $data): GiftCard
    {
        return resolve(CreateGiftCard::class)(CreateGiftCardData::fromArray($data));
    }
}
