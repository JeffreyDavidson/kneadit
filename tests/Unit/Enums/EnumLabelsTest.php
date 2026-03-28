<?php

use App\Enums\CateringEventType;
use App\Enums\CateringInquiryStatus;
use App\Enums\ExpenseCategory;
use App\Enums\IncomeSource;
use App\Enums\TaxExportType;

dataset('enumsWithLabels', [
    'CateringEventType' => [CateringEventType::cases()],
    'CateringInquiryStatus' => [CateringInquiryStatus::cases()],
    'ExpenseCategory' => [ExpenseCategory::cases()],
    'IncomeSource' => [IncomeSource::cases()],
    'TaxExportType' => [TaxExportType::cases()],
]);

test('all cases have non-empty labels', function (array $cases) {
    foreach ($cases as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty();
    }
})->with('enumsWithLabels');
