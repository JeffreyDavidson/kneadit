<?php

use App\Enums\WaitlistStatus;
use App\Models\WaitlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('waiting scope returns only entries with waiting status', function () {
    $waiting = WaitlistEntry::factory()->create(['status' => WaitlistStatus::Waiting]);
    WaitlistEntry::factory()->create(['status' => WaitlistStatus::Notified]);

    $results = WaitlistEntry::query()->waiting()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($waiting->id);
});
