<?php

namespace App\Actions\Staff;

use App\Enums\UserRole;
use App\Events\StaffInvitationSent;
use App\Exceptions\StaffInvitationException;
use App\Models\StaffInvitation;
use App\Models\User;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Str;

class SendStaffInvitation
{
    public function __construct(
        private TenantSettings $settings,
    ) {}

    /**
     * @throws StaffInvitationException
     */
    public function __invoke(string $email, UserRole $role, int $invitedBy): StaffInvitation
    {
        if (User::query()->where('email', $email)->exists()) {
            throw StaffInvitationException::alreadyTeamMember($email);
        }

        $existing = StaffInvitation::query()->where('email', $email)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->exists();

        if ($existing) {
            throw StaffInvitationException::pendingInvitation($email);
        }

        $invitation = StaffInvitation::query()->create([
            'email' => $email,
            'role' => $role->value,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(config('kneadit.invitation_expiry_days', 7)),
            'invited_by' => $invitedBy,
        ]);

        $storeName = $this->settings->storeName;
        $acceptUrl = route('invitation.show', $invitation->token);

        StaffInvitationSent::dispatch($invitation, $storeName, $acceptUrl);

        return $invitation;
    }
}
