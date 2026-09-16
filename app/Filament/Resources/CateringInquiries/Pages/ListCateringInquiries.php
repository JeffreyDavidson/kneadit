<?php

namespace App\Filament\Resources\CateringInquiries\Pages;

use App\Filament\Resources\CateringInquiries\CateringInquiryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCateringInquiries extends ListRecords
{
    #[\Override]
    protected static string $resource = CateringInquiryResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver(),
        ];
    }
}
