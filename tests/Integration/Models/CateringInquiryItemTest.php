<?php

use App\Models\Customers\CateringInquiry;
use App\Models\Customers\CateringInquiryItem;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('belongs to a catering inquiry', function () {
    $inquiry = CateringInquiry::factory()->create();
    $item = CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create();

    expect($item->inquiry->id)->toBe($inquiry->id);
});

test('line_total accessor multiplies unit_price by quantity', function () {
    $item = CateringInquiryItem::factory()->create([
        'unit_price' => 25.00,
        'quantity' => 4,
    ]);

    expect($item->line_total)->toBeInstanceOf(Money::class)
        ->and($item->line_total->dollars())->toBe(100.00);
});

test('items() relation orders by sort_order then id', function () {
    $inquiry = CateringInquiry::factory()->create();
    $second = CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create(['sort_order' => 2, 'name' => 'Macarons']);
    $first = CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create(['sort_order' => 1, 'name' => 'Cake']);

    expect($inquiry->items->pluck('name')->all())->toBe(['Cake', 'Macarons']);
});
