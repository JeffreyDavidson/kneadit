<?php

use App\Builders\Staff\StaffInvitationQueryBuilder;
use App\Models\Staff\StaffInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

function staffInvitationQuery(): StaffInvitationQueryBuilder
{
    return StaffInvitation::query();
}

beforeEach(fn () => setUpTenantTest());

test('unexpired excludes expired invitations', function () {
    StaffInvitation::factory()->create();
    StaffInvitation::factory()->expired()->create();

    $builder = staffInvitationQuery();
    $query = (new ReflectionMethod($builder, 'unexpired'))->invoke($builder);
    throw_unless($query instanceof StaffInvitationQueryBuilder, RuntimeException::class, 'Expected the custom invitation builder.');
    $invitations = $query->get();

    expect($invitations)->toHaveCount(1);
});

test('pendingAndUnexpired excludes accepted and expired invitations', function () {
    $pending = StaffInvitation::factory()->create();
    StaffInvitation::factory()->accepted()->create();
    StaffInvitation::factory()->expired()->create();

    $builder = staffInvitationQuery();
    $query = (new ReflectionMethod($builder, 'pendingAndUnexpired'))->invoke($builder);
    throw_unless($query instanceof StaffInvitationQueryBuilder, RuntimeException::class, 'Expected the custom invitation builder.');
    $invitations = $query->get();

    expect($invitations)->toHaveCount(1)
        ->and($invitations->firstOrFail()->is($pending))->toBeTrue();
});
