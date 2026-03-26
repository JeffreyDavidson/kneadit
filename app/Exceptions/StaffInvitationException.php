<?php

namespace App\Exceptions;

use RuntimeException;

class StaffInvitationException extends RuntimeException
{
    public static function alreadyTeamMember(string $email): self
    {
        return new self("This person is already a team member: {$email}");
    }

    public static function pendingInvitation(string $email): self
    {
        return new self("An invitation is already pending for: {$email}");
    }
}
