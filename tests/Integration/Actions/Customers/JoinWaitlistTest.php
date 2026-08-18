<?php

use App\Actions\Customers\JoinWaitlist;
use App\Enums\Customers\WaitlistStatus;
use App\Models\Customers\WaitlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it creates a waitlist entry with waiting status', function () {
    $action = new JoinWaitlist;
    $entry = $action([
        'customer_name' => 'Jane Baker',
        'customer_email' => 'jane@example.com',
        'requested_date' => '2026-06-15',
    ]);

    expect($entry)->toBeInstanceOf(WaitlistEntry::class)
        ->and($entry->customer_name)->toBe('Jane Baker')
        ->and($entry->status)->toBe(WaitlistStatus::Waiting);
});
