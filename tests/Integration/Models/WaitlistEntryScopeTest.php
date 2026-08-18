<?php

use App\Models\Customers\WaitlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('waiting scope returns only entries with waiting status', function () {
    $waiting = WaitlistEntry::factory()->waiting()->create();
    WaitlistEntry::factory()->notified()->create();

    $results = WaitlistEntry::query()->waiting()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($waiting->id);
});
