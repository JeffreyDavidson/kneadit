<?php

use App\Actions\Inventory\RemoveSeasonalItem;
use App\Models\Inventory\SeasonalItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('removes a seasonal item', function () {
    $seasonalItem = SeasonalItem::factory()->create();

    resolve(RemoveSeasonalItem::class)($seasonalItem->id);

    expect(SeasonalItem::query()->find($seasonalItem->id))->toBeNull();
});

test('fails when the seasonal item does not exist', function () {
    expect(fn () => resolve(RemoveSeasonalItem::class)(99999))
        ->toThrow(ModelNotFoundException::class);
});
