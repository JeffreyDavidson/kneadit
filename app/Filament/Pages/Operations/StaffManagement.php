<?php

namespace App\Filament\Pages\Operations;

use App\Actions\Staff\ChangeStaffRole;
use App\Actions\Staff\RemoveStaffMember;
use App\Actions\Staff\RevokeStaffInvitation;
use App\Actions\Staff\SendStaffInvitation;
use App\Enums\Staff\UserRole;
use App\Exceptions\Staff\StaffInvitationException;
use App\Models\Staff\StaffInvitation;
use App\Models\Staff\User;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use UnitEnum;

class StaffManagement extends Page
{
    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user instanceof User || ! $user->role->meetsRequirement(UserRole::Owner)) {
            return false;
        }

        return true;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Admin';

    protected static ?string $navigationLabel = 'Staff';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.operations.staff-management';

    public string $inviteEmail = '';

    public string $inviteRole = UserRole::Staff->value;

    public function getTitle(): string
    {
        return 'Team Management';
    }

    /** @return Collection<int, User> */
    public function getTeamMembers(): Collection
    {
        return User::query()
            ->orderByRaw('CASE role WHEN ? THEN 1 WHEN ? THEN 2 WHEN ? THEN 3 ELSE 4 END', [
                UserRole::Owner->value,
                UserRole::Manager->value,
                UserRole::Staff->value,
            ])
            ->get();
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
