<?php

use App\Models\Customers\CateringInquiry;
use App\Models\Customers\CateringInquiryItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('creating an item recomputes the parent quoted_amount', function () {
    $inquiry = CateringInquiry::factory()->create(['quoted_amount' => 0]);

    CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create([
        'unit_price' => 100.00,
        'quantity' => 3,
    ]);

    expect($inquiry->refresh()->quoted_amount?->dollars())->toBe(300.00);
});

test('updating an item recomputes the parent quoted_amount', function () {
    $inquiry = CateringInquiry::factory()->create();
    $item = CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create([
        'unit_price' => 50.00,
        'quantity' => 2,
    ]);

    $item->update(['quantity' => 5]);

    expect($inquiry->refresh()->quoted_amount?->dollars())->toBe(250.00);
});

test('deleting an item recomputes the parent quoted_amount', function () {
    $inquiry = CateringInquiry::factory()->create();
    CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create(['unit_price' => 100, 'quantity' => 1]);
    $delete = CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create(['unit_price' => 25, 'quantity' => 4]);

    $delete->delete();

    expect($inquiry->refresh()->quoted_amount?->dollars())->toBe(100.00);
});

test('removing the last item zeroes the parent quoted_amount', function () {
    $inquiry = CateringInquiry::factory()->create();
    $only = CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create(['unit_price' => 50, 'quantity' => 1]);

    $only->delete();

    expect($inquiry->refresh()->quoted_amount?->dollars())->toBe(0.00);
});

test('multiple items sum correctly', function () {
    $inquiry = CateringInquiry::factory()->create();

    CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create(['unit_price' => 50, 'quantity' => 2]); // 100
    CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create(['unit_price' => 25, 'quantity' => 4]); // 100
    CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create(['unit_price' => 200, 'quantity' => 1]); // 200

    expect($inquiry->refresh()->quoted_amount?->dollars())->toBe(400.00);
});
