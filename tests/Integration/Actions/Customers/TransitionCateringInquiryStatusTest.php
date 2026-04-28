<?php

use App\Actions\Customers\TransitionCateringInquiryStatus;
use App\Enums\Customers\CateringInquiryStatus;
use App\Models\Customers\CateringInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('transitions catering inquiry to the given status', function (CateringInquiryStatus $from, CateringInquiryStatus $to) {
    $inquiry = CateringInquiry::factory()->create(['status' => $from]);

    resolve(TransitionCateringInquiryStatus::class)($inquiry, $to);

    expect($inquiry->fresh()->status)->toBe($to);
})->with([
    'inquiry to quoted' => [CateringInquiryStatus::Inquiry, CateringInquiryStatus::Quoted],
    'quoted to confirmed' => [CateringInquiryStatus::Quoted, CateringInquiryStatus::Confirmed],
    'confirmed to completed' => [CateringInquiryStatus::Confirmed, CateringInquiryStatus::Completed],
    'inquiry to cancelled' => [CateringInquiryStatus::Inquiry, CateringInquiryStatus::Cancelled],
]);
