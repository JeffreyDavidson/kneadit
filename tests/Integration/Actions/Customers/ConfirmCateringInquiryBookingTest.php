<?php

use App\Actions\Customers\ConfirmCateringInquiryBooking;
use App\Enums\Customers\CateringInquiryStatus;
use App\Enums\Orders\OrderStatus;
use App\Events\Orders\OrderCreated;
use App\Models\Customers\CateringInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('confirms a catering inquiry booking by creating its order and transitioning the inquiry', function () {
    Event::fake([OrderCreated::class]);

    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
        'quoted_amount' => 1250,
    ]);

    $order = resolve(ConfirmCateringInquiryBooking::class)($inquiry);

    expect($order->status)->toBe(OrderStatus::Confirmed)
        ->and($order->catering_inquiry_id)->toBe($inquiry->id)
        ->and($inquiry->fresh()->status)->toBe(CateringInquiryStatus::Confirmed);

    Event::assertDispatched(OrderCreated::class);
});
