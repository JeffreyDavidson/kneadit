<?php

use App\Actions\Customers\CancelCateringInquiry;
use App\Enums\Customers\CateringInquiryStatus;
use App\Enums\Orders\OrderStatus;
use App\Events\Marketing\CateringQuoteRequested;
use App\Models\Customers\CateringInquiry;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('transitions inquiry to cancelled', function () {
    Event::fake();

    $inquiry = CateringInquiry::factory()->create(['status' => CateringInquiryStatus::Quoted]);

    resolve(CancelCateringInquiry::class)($inquiry);

    expect($inquiry->fresh()->status)->toBe(CateringInquiryStatus::Cancelled);
    Event::assertNotDispatched(CateringQuoteRequested::class);
});

test('prepends a stamped reason to notes when provided', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Inquiry,
        'notes' => 'Existing internal note.',
    ]);

    resolve(CancelCateringInquiry::class)($inquiry, 'Customer chose another vendor');

    $notes = $inquiry->fresh()->notes;
    expect($notes)->toContain('Cancelled: Customer chose another vendor')
        ->and($notes)->toContain('Existing internal note.')
        ->and(str_starts_with($notes, '['))->toBeTrue();
});

test('ignores blank reasons', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Inquiry,
        'notes' => 'Existing.',
    ]);

    resolve(CancelCateringInquiry::class)($inquiry, '   ');

    expect($inquiry->fresh()->notes)->toBe('Existing.');
});

test('cascades cancellation to a linked order in a cancellable state', function () {
    $inquiry = CateringInquiry::factory()->create(['status' => CateringInquiryStatus::Confirmed]);
    $order = Order::factory()->for($inquiry, 'cateringInquiry')->create(['status' => OrderStatus::Confirmed]);

    resolve(CancelCateringInquiry::class)($inquiry);

    expect($inquiry->fresh()->status)->toBe(CateringInquiryStatus::Cancelled)
        ->and($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

test('leaves the order alone when it is in a non-cancellable terminal state', function () {
    $inquiry = CateringInquiry::factory()->create(['status' => CateringInquiryStatus::Confirmed]);
    $order = Order::factory()->for($inquiry, 'cateringInquiry')->create(['status' => OrderStatus::Delivered]);

    resolve(CancelCateringInquiry::class)($inquiry);

    expect($inquiry->fresh()->status)->toBe(CateringInquiryStatus::Cancelled)
        ->and($order->fresh()->status)->toBe(OrderStatus::Delivered);
});
