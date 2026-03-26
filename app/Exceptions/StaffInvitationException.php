<?php

namespace App\Exceptions;

use RuntimeException;

class StaffInvitationException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $email,
    ) {
        parent::__construct($message);
    }

    public static function alreadyTeamMember(string $email): self
    {
        return new self("This person is already a team member: {$email}", $email);
    }

    public static function pendingInvitation(string $email): self
    {
        return new self("An invitation is already pending for: {$email}", $email);
    }

    /** @return array<string, string> */
    public function context(): array
    {
        return ['email' => $this->email];
    }
}
