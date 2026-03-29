<?php

use App\Actions\Customers\UpdateWaitlistEntryStatus;
use App\Enums\WaitlistStatus;
use App\Models\WaitlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('it updates a waitlist entry status', function () {
    $entry = WaitlistEntry::factory()->create();

    resolve(UpdateWaitlistEntryStatus::class)($entry, WaitlistStatus::Notified);

    expect($entry->fresh()->status)->toBe(WaitlistStatus::Notified);
});
