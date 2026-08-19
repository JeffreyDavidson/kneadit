<?php

use App\Models\Staff\StaffInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('unexpired excludes expired invitations', function () {
    StaffInvitation::factory()->create();
    StaffInvitation::factory()->expired()->create();

    $invitations = StaffInvitation::query()->unexpired()->get();

    expect($invitations)->toHaveCount(1);
});

test('pendingAndUnexpired excludes accepted and expired invitations', function () {
    $pending = StaffInvitation::factory()->create();
    StaffInvitation::factory()->accepted()->create();
    StaffInvitation::factory()->expired()->create();

    $invitations = StaffInvitation::query()->pendingAndUnexpired()->get();

    expect($invitations)->toHaveCount(1)
        ->and($invitations->first()?->is($pending))->toBeTrue();
});
