<?php

use App\Actions\Customers\SyncCateringQuoteItems;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\CateringInquiryItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('synchronizes quote items and recomputes the quoted total', function () {
    $inquiry = CateringInquiry::factory()->create();
    $updatedItem = CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create([
        'name' => 'Original cake',
        'quantity' => 1,
        'unit_price' => 100,
        'sort_order' => 0,
    ]);
    $removedItem = CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create([
        'name' => 'Remove me',
        'quantity' => 1,
        'unit_price' => 50,
        'sort_order' => 1,
    ]);

    resolve(SyncCateringQuoteItems::class)($inquiry, [
        [
            'id' => null,
            'name' => 'Macarons',
            'quantity' => 20,
            'unit_price' => 3.0,
            'special_instructions' => null,
        ],
        [
            'id' => $updatedItem->id,
            'name' => 'Wedding cake',
            'quantity' => 2,
            'unit_price' => 125.0,
            'special_instructions' => 'Vanilla',
        ],
    ]);

    $items = $inquiry->items()->get();

    expect($removedItem->fresh())->toBeNull()
        ->and($items)->toHaveCount(2)
        ->and($items->pluck('name')->all())->toBe(['Macarons', 'Wedding cake'])
        ->and($items->pluck('sort_order')->all())->toBe([0, 1])
        ->and($items->last()?->quantity)->toBe(2)
        ->and($items->last()?->special_instructions)->toBe('Vanilla')
        ->and($inquiry->quoted_amount?->dollars())->toBe(310.0);
});
