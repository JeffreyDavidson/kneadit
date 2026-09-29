<?php

use App\Actions\Customers\SyncCateringQuoteItems;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\CateringInquiryItem;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

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

test('recalculates the quote total once with a database aggregate', function () {
    $inquiry = CateringInquiry::factory()->create();
    $item = CateringInquiryItem::factory()->for($inquiry, 'inquiry')->create([
        'name' => 'Cookies',
        'quantity' => 2,
        'unit_price' => 12.34,
        'special_instructions' => null,
        'sort_order' => 0,
    ]);

    $itemSelects = [];
    $inquiryUpdates = [];
    DB::listen(function (QueryExecuted $query) use (&$itemSelects, &$inquiryUpdates): void {
        $sql = ltrim(strtolower(str_replace(['"', '`', '[', ']'], '', $query->sql)));

        if (str_starts_with($sql, 'select') && str_contains($sql, 'from catering_inquiry_items')) {
            $itemSelects[] = $sql;
        }

        if (str_starts_with($sql, 'update catering_inquiries ')) {
            $inquiryUpdates[] = $sql;
        }
    });

    resolve(SyncCateringQuoteItems::class)($inquiry, [
        [
            'id' => $item->id,
            'name' => 'Cookies',
            'quantity' => 3,
            'unit_price' => 12.34,
            'special_instructions' => null,
        ],
        [
            'id' => null,
            'name' => 'Brownies',
            'quantity' => 1,
            'unit_price' => 5.0,
            'special_instructions' => null,
        ],
    ]);

    expect($inquiry->quoted_amount?->dollars())->toBe(42.02)
        ->and($itemSelects)->toHaveCount(2)
        ->and($itemSelects[1])->toContain('sum(unit_price * quantity)')
        ->and($inquiryUpdates)->toHaveCount(1);
});

test('sets the quote total to zero when no items remain', function () {
    $inquiry = CateringInquiry::factory()->create(['quoted_amount' => 12.34]);

    resolve(SyncCateringQuoteItems::class)($inquiry, []);

    expect($inquiry->quoted_amount?->cents())->toBe(0);
});
