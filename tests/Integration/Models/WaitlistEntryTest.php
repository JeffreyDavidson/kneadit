<?php

use App\Enums\Customers\WaitlistStatus;
use App\Models\Customers\WaitlistEntry;
use App\Models\Inventory\Product;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('product relationship returns the associated product', function () {
    $product = Product::factory()->create();
    $entry = WaitlistEntry::factory()->create([
        'product_id' => $product->id,
    ]);

    expect($entry->product?->id)->toBe($product->id);
});

test('status is cast to WaitlistStatus enum', function () {
    $entry = WaitlistEntry::factory()->create([
        'status' => WaitlistStatus::Waiting,
    ]);

    $entry->refresh();

    expect($entry->getAttribute('status'))->toBe(WaitlistStatus::Waiting);
});

test('requested date is cast to date', function () {
    $entry = WaitlistEntry::factory()->create([
        'requested_date' => '2026-05-01',
    ]);

    $entry->refresh();

    expect($entry->requested_date)->toBeInstanceOf(Carbon::class);
});

test('for date scope filters by requested date', function () {
    $entry = WaitlistEntry::factory()->create([
        'requested_date' => '2026-06-15',
    ]);
    WaitlistEntry::factory()->create([
        'requested_date' => '2026-06-20',
    ]);

    $results = WaitlistEntry::query()->forDate('2026-06-15')->get();

    expect($results)->toHaveCount(1)
        ->and($results->firstOrFail()->id)->toBe($entry->id);
});

test('for date scope accepts Carbon instance', function () {
    $entry = WaitlistEntry::factory()->create([
        'requested_date' => '2026-07-04',
    ]);

    $results = WaitlistEntry::query()->forDate(Illuminate\Support\Facades\Date::parse('2026-07-04'))->get();

    expect($results)->toHaveCount(1)
        ->and($results->firstOrFail()->id)->toBe($entry->id);
});
