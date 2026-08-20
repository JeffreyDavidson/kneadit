<?php

use App\Actions\Customers\SendCateringQuote;
use App\Enums\Customers\CateringInquiryStatus;
use App\Events\Marketing\CateringQuoteRequested;
use App\Models\Customers\CateringInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('transitions Inquiry to Quoted and dispatches the quote event', function () {
    Event::fake();

    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Inquiry,
        'quoted_amount' => 500.00,
    ]);

    resolve(SendCateringQuote::class)($inquiry);

    expect($inquiry->refresh()->status)->toBe(CateringInquiryStatus::Quoted);
    Event::assertDispatched(CateringQuoteRequested::class);
});
