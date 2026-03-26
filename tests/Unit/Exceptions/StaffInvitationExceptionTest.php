<?php

use App\Exceptions\StaffInvitationException;

it('stores email and provides context for alreadyTeamMember', function () {
    $exception = StaffInvitationException::alreadyTeamMember('test@example.com');

    expect($exception->email)->toBe('test@example.com')
        ->and($exception->context())->toBe(['email' => 'test@example.com']);
});
