<?php

use App\Actions\Customers\TransitionCateringInquiryStatus;
use App\Enums\CateringInquiryStatus;
use App\Events\Marketing\CateringQuoteRequested;
use App\Models\CateringInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('transitions catering inquiry from inquiry to quoted and dispatches event', function () {
    Event::fake();

    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Inquiry,
        'quoted_amount' => 500.00,
    ]);

    resolve(TransitionCateringInquiryStatus::class)($inquiry, CateringInquiryStatus::Quoted);

    expect($inquiry->fresh()->status)->toBe(CateringInquiryStatus::Quoted);
    Event::assertDispatched(CateringQuoteRequested::class);
});

test('transitions to confirmed without dispatching quote event', function () {
    Event::fake();

    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
    ]);

    resolve(TransitionCateringInquiryStatus::class)($inquiry, CateringInquiryStatus::Confirmed);

    expect($inquiry->fresh()->status)->toBe(CateringInquiryStatus::Confirmed);
    Event::assertNotDispatched(CateringQuoteRequested::class);
});
