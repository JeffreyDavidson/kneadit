<?php

use App\Actions\Customers\ConvertCateringInquiryToOrder;
use App\Enums\Customers\CateringInquiryStatus;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Events\Orders\OrderCreated;
use App\Exceptions\Customers\InquiryNotConvertibleException;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\CateringInquiryItem;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('creates a confirmed order with the quote total + a single line item, transitions inquiry to Confirmed, fires OrderCreated', function () {
    Event::fake([OrderCreated::class]);

    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
        'quoted_amount' => 4200,
        'customer_name' => 'Maya Patel',
        'customer_email' => 'maya@example.com',
        'event_type' => 'Wedding',
        'guest_count' => 120,
    ]);

    $order = resolve(ConvertCateringInquiryToOrder::class)($inquiry);

    expect($order->status)->toBe(OrderStatus::Confirmed)
        ->and($order->payment_status)->toBe(PaymentStatus::Unpaid)
        ->and($order->total->dollars())->toBe(4200.00)
        ->and($order->subtotal->dollars())->toBe(4200.00)
        ->and($order->catering_inquiry_id)->toBe($inquiry->id)
        ->and($order->orderItems)->toHaveCount(1)
        ->and($order->orderItems->first()->name)->toBe('Catering — Wedding, 120 guests')
        ->and($order->orderItems->first()->unit_price->dollars())->toBe(4200.00)
        ->and($inquiry->fresh()->status)->toBe(CateringInquiryStatus::Confirmed);

    Event::assertDispatched(OrderCreated::class);
});

test('sets payment_status to Partial when a deposit was already received', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
        'quoted_amount' => 1000,
        'deposit_amount' => 250,
        'deposit_paid_at' => now(),
    ]);

    $order = resolve(ConvertCateringInquiryToOrder::class)($inquiry);

    expect($order->payment_status)->toBe(PaymentStatus::Partial);
});

test('is idempotent: calling twice returns the same order, no duplicate items', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
        'quoted_amount' => 800,
    ]);

    $first = resolve(ConvertCateringInquiryToOrder::class)($inquiry);
    $second = resolve(ConvertCateringInquiryToOrder::class)($inquiry);

    expect($first->id)->toBe($second->id)
        ->and($first->orderItems()->count())->toBe(1);
});

test('reuses an existing customer matched by email', function () {
    $existing = Customer::factory()->create(['email' => 'shared@example.com']);

    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
        'quoted_amount' => 500,
        'customer_email' => 'shared@example.com',
    ]);

    $order = resolve(ConvertCateringInquiryToOrder::class)($inquiry);

    expect($order->customer_id)->toBe($existing->id)
        ->and(Customer::query()->where('email', 'shared@example.com')->count())->toBe(1);
});

test('throws when the inquiry has no quoted amount', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Inquiry,
        'quoted_amount' => null,
    ]);

    resolve(ConvertCateringInquiryToOrder::class)($inquiry);
})->throws(InquiryNotConvertibleException::class);

test('copies the event date onto the order delivery_date', function () {
    $eventDate = now()->addMonths(3)->startOfDay();

    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
        'quoted_amount' => 600,
        'event_date' => $eventDate,
    ]);

    $order = resolve(ConvertCateringInquiryToOrder::class)($inquiry);

    expect($order->delivery_date->toDateString())->toBe($eventDate->toDateString());
});

test('copies inquiry items to OrderItems when items exist (no collapsed line)', function () {
    $inquiry = CateringInquiry::factory()->create(['status' => CateringInquiryStatus::Quoted]);

    CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create([
        'name' => 'Wedding cake',
        'quantity' => 1,
        'unit_price' => 400,
        'special_instructions' => 'Three tiers, ivory frosting',
        'sort_order' => 0,
    ]);
    CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create([
        'name' => 'Macarons',
        'quantity' => 100,
        'unit_price' => 3,
        'sort_order' => 1,
    ]);

    $order = resolve(ConvertCateringInquiryToOrder::class)($inquiry->fresh());

    expect($order->orderItems)->toHaveCount(2)
        ->and($order->orderItems->pluck('name')->all())->toContain('Wedding cake', 'Macarons')
        ->and($order->total->dollars())->toBe(700.00);

    $cake = $order->orderItems->firstWhere('name', 'Wedding cake');
    expect($cake->special_instructions)->toBe('Three tiers, ivory frosting')
        ->and($cake->unit_price->dollars())->toBe(400.00);
});

test('falls back to single collapsed line when the inquiry has no items', function () {
    $inquiry = CateringInquiry::factory()->create([
        'status' => CateringInquiryStatus::Quoted,
        'quoted_amount' => 1500,
        'event_type' => 'Wedding',
        'guest_count' => 80,
    ]);

    $order = resolve(ConvertCateringInquiryToOrder::class)($inquiry);

    expect($order->orderItems)->toHaveCount(1)
        ->and($order->orderItems->first()->name)->toBe('Catering — Wedding, 80 guests')
        ->and($order->orderItems->first()->unit_price->dollars())->toBe(1500.00);
});
