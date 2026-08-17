<?php

use App\Actions\Customers\UpdateWaitlistEntryStatus;
use App\Enums\Customers\WaitlistStatus;
use App\Models\Customers\WaitlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('it updates a waitlist entry status', function () {
    $entry = WaitlistEntry::factory()->create();

    resolve(UpdateWaitlistEntryStatus::class)($entry, WaitlistStatus::Notified);

    expect($entry->refresh()->status)->toBe(WaitlistStatus::Notified);
});
