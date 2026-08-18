<?php

use App\Actions\Customers\RecordCateringDeposit;
use App\Enums\Customers\CateringInquiryStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\CateringInquiry;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('stamps deposit_amount + paid_at + reference', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
        'quoted_amount' => 1000.00,
    ]);

    resolve(RecordCateringDeposit::class)($inquiry, 250.00, 'CHK-1234');

    $inquiry->refresh();
    expect($inquiry->deposit_amount->dollars())->toBe(250.00)
        ->and($inquiry->deposit_paid_at)->not->toBeNull()
        ->and($inquiry->deposit_reference)->toBe('CHK-1234');
});

test('promotes Quoted → Confirmed when deposit recorded', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
        'quoted_amount' => 500.00,
    ]);

    resolve(RecordCateringDeposit::class)($inquiry, 125.00);

    expect($inquiry->fresh()->status)->toBe(CateringInquiryStatus::Confirmed);
});

test('does not change status when already Confirmed', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Confirmed,
        'quoted_amount' => 500.00,
    ]);

    resolve(RecordCateringDeposit::class)($inquiry, 125.00);

    expect($inquiry->fresh()->status)->toBe(CateringInquiryStatus::Confirmed);
});

test('treats blank reference as null', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
        'quoted_amount' => 500.00,
    ]);

    resolve(RecordCateringDeposit::class)($inquiry, 100.00, '   ');

    expect($inquiry->fresh()->deposit_reference)->toBeNull();
});

test('clamps negative amount to 0', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
        'quoted_amount' => 500.00,
    ]);

    resolve(RecordCateringDeposit::class)($inquiry, -50.00);

    expect($inquiry->fresh()->deposit_amount->dollars())->toBe(0.00);
});

test('suggestedAmount returns the configured percent of the quote', function () {
    $inquiry = CateringInquiry::factory()->create(['quoted_amount' => 800.00]);

    expect(resolve(RecordCateringDeposit::class)->suggestedAmount($inquiry, 25))->toBe(200.00);
});

test('suggestedAmount returns 0 when no quote', function () {
    $inquiry = CateringInquiry::factory()->create(['quoted_amount' => null]);

    expect(resolve(RecordCateringDeposit::class)->suggestedAmount($inquiry, 25))->toBe(0.0);
});

test('marks a linked Unpaid order as Partial when deposit is recorded', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Confirmed,
        'quoted_amount' => 1000,
    ]);
    $order = Order::factory()->for($inquiry, 'cateringInquiry')->create([
        'payment_status' => PaymentStatus::Unpaid,
    ]);

    resolve(RecordCateringDeposit::class)($inquiry, 250.00);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Partial);
});

test('does not downgrade a linked order whose payment_status is already Paid', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Confirmed,
        'quoted_amount' => 1000,
    ]);
    $order = Order::factory()->for($inquiry, 'cateringInquiry')->create([
        'payment_status' => PaymentStatus::Paid,
    ]);

    resolve(RecordCateringDeposit::class)($inquiry, 250.00);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Paid);
});
