<?php

use App\Enums\Marketing\BulkMessagePurpose;

test('every purpose has a label, a description and a skip reason', function (BulkMessagePurpose $purpose) {
    expect($purpose->getLabel())->toBeString()->not->toBeEmpty()
        ->and($purpose->getDescription())->toBeString()->not->toBeEmpty()
        ->and($purpose->skipReason())->toBeString()->not->toBeEmpty();
})->with(BulkMessagePurpose::cases());

test('purposes are backed by stable strings', function () {
    expect(BulkMessagePurpose::OrderUpdate->value)->toBe('order_update')
        ->and(BulkMessagePurpose::Promotion->value)->toBe('promotion')
        ->and(BulkMessagePurpose::OrderUpdate->getLabel())->toBe('Order update')
        ->and(BulkMessagePurpose::Promotion->getLabel())->toBe('Promotion');
});
