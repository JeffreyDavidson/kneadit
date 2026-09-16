<?php

use App\Enums\Customers\CateringInquiryStatus;
use Filament\Support\Icons\Heroicon;

test('CateringInquiryStatus has a color for every case', function (CateringInquiryStatus $case) {
    expect($case->getColor())->toBeString();
})->with(CateringInquiryStatus::cases());

test('CateringInquiryStatus has an icon for every case', function (CateringInquiryStatus $case) {
    expect($case->getIcon())->toBeInstanceOf(Heroicon::class);
})->with(CateringInquiryStatus::cases());
