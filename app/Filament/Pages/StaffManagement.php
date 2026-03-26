<?php

namespace App\Filament\Pages;

use App\Actions\Staff\ChangeStaffRole;
use App\Actions\Staff\RemoveStaffMember;
use App\Actions\Staff\RevokeStaffInvitation;
use App\Actions\Staff\SendStaffInvitation;
use App\Enums\UserRole;
use App\Exceptions\StaffInvitationException;
use App\Models\StaffInvitation;
use App\Models\User;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use UnitEnum;

class StaffManagement extends Page
{
    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Owner)) {
            return false;
        }

        return true;
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|UnitEnum|null $navigationGroup = 'Admin';

    protected static ?string $navigationLabel = 'Staff';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.staff-management';

    public string $inviteEmail = '';

    public string $inviteRole = UserRole::Staff->value;

    public function getTitle(): string
    {
        return 'Team Management';
    }

    /** @return Collection<int, User> */
    public function getTeamMembers(): Collection
    {
        $roles = [UserRole::Owner->value, UserRole::Manager->value, UserRole::Staff->value];
        $placeholders = implode(',', array_fill(0, count($roles), '?'));

        return User::query()->orderByRaw("FIELD(role, {$placeholders})", $roles)->get();
    }

    /** @return Collection<int, StaffInvitation> */
    public function getPendingInvitations(): Collection
    {
        return StaffInvitation::query()->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->get();
    }

    public function sendInvitation(): void
    {
        $this->validate([
            'inviteEmail' => ['required', 'email'],
            'inviteRole' => ['required', Rule::in([UserRole::Manager->value, UserRole::Staff->value])],
        ]);

        try {
            resolve(SendStaffInvitation::class)(
                email: $this->inviteEmail,
                role: UserRole::from($this->inviteRole),
                invitedBy: (int) Auth::id(),
            );

            $this->inviteEmail = '';
            $this->inviteRole = UserRole::Staff->value;

            Notification::make()
                ->title('Invitation sent!')
                ->success()
                ->send();
        } catch (StaffInvitationException $e) {
            Notification::make()
                ->title($e->getMessage())
                ->warning()
                ->send();
        }
    }

    public function revokeInvitation(int $id): void
    {
        resolve(RevokeStaffInvitation::class)($id);

        Notification::make()
            ->title('Invitation revoked')
            ->success()
            ->send();
    }

    public function changeRole(int $userId, string $newRole): void
    {
        $role = UserRole::tryFrom($newRole);
        if (! $role) {
            return;
        }

        try {
            resolve(ChangeStaffRole::class)($userId, $role, (int) Auth::id());

            Notification::make()
                ->title("Role updated to {$newRole}")
                ->success()
                ->send();
        } catch (\RuntimeException $e) {
            Notification::make()
                ->title($e->getMessage())
                ->warning()
                ->send();
        }
    }

    public function removeMember(int $userId): void
    {
        try {
            resolve(RemoveStaffMember::class)($userId, (int) Auth::id());

            Notification::make()
                ->title('Team member removed')
                ->success()
                ->send();
        } catch (\RuntimeException $e) {
            Notification::make()
                ->title($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
