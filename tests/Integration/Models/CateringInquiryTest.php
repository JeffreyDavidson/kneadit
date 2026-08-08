<?php

use App\Enums\Customers\CateringInquiryStatus;
use App\Models\Customers\CateringInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('status is cast to CateringInquiryStatus enum', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Inquiry,
    ]);

    expect($inquiry->fresh()->status)->toBe(CateringInquiryStatus::Inquiry);
});
