<?php

use App\Exceptions\Staff\StaffInvitationException;

it('stores email and provides context for alreadyTeamMember', function () {
    $exception = StaffInvitationException::alreadyTeamMember('test@example.com');

    expect($exception->email)->toBe('test@example.com')
        ->and($exception->context())->toBe(['email' => 'test@example.com']);
});

it('stores email and provides message for pendingInvitation', function () {
    $exception = StaffInvitationException::pendingInvitation('pending@example.com');

    expect($exception->getMessage())->toContain('pending@example.com')
        ->and($exception->email)->toBe('pending@example.com');
});
