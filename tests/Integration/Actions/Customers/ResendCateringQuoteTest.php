<?php

use App\Actions\Customers\ResendCateringQuote;
use App\Enums\Customers\CateringInquiryStatus;
use App\Events\Marketing\CateringQuoteRequested;
use App\Models\Customers\CateringInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('redispatches the quote event without touching status', function () {
    Event::fake();

    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
        'quoted_amount' => 500.00,
    ]);

    resolve(ResendCateringQuote::class)($inquiry);

    expect($inquiry->fresh()->status)->toBe(CateringInquiryStatus::Quoted);
    Event::assertDispatched(CateringQuoteRequested::class);
});
