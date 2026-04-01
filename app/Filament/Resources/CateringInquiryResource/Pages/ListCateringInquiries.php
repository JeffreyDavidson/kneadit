<?php

namespace App\Filament\Resources\CateringInquiryResource\Pages;

use App\Filament\Resources\CateringInquiryResource\CateringInquiryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCateringInquiries extends ListRecords
{
    protected static string $resource = CateringInquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver(),
        ];
    }
}
