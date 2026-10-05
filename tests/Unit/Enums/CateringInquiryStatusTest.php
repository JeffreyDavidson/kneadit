<?php

use App\Enums\Customers\CateringInquiryStatus;
use Filament\Support\Icons\Heroicon;

test('CateringInquiryStatus has a color for every case', function (CateringInquiryStatus $case) {
    expect($case->getColor())->toBeString();
})->with(CateringInquiryStatus::cases());

test('CateringInquiryStatus has an icon for every case', function (CateringInquiryStatus $case) {
    expect($case->getIcon())->toBeInstanceOf(Heroicon::class);
})->with(CateringInquiryStatus::cases());

test('CateringInquiryStatus accepts a deposit only while quoted or confirmed', function (CateringInquiryStatus $case, bool $accepts) {
    expect($case->acceptsDeposit())->toBe($accepts);
})->with([
    'inquiry' => [CateringInquiryStatus::Inquiry, false],
    'quoted' => [CateringInquiryStatus::Quoted, true],
    'confirmed' => [CateringInquiryStatus::Confirmed, true],
    'completed' => [CateringInquiryStatus::Completed, false],
    'cancelled' => [CateringInquiryStatus::Cancelled, false],
]);
