<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Mail\StaffInvitationMail;
use App\Models\Setting;
use App\Models\StaffInvitation;
use App\Models\User;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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

    /** @return Collection<int, Model> */
    public function getTeamMembers(): Collection
    {
        $roleOrder = collect([UserRole::Owner, UserRole::Manager, UserRole::Staff])
            ->map(fn (UserRole $role) => "'{$role->value}'")
            ->implode(', ');

        return User::query()->orderByRaw("FIELD(role, {$roleOrder})")->get();
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

        // Check if already a team member
        if (User::query()->where('email', $this->inviteEmail)->exists()) {
            Notification::make()
                ->title('This person is already a team member')
                ->warning()
                ->send();

            return;
        }

        // Check for existing pending invitation
        $existing = StaffInvitation::query()->where('email', $this->inviteEmail)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            Notification::make()
                ->title('An invitation is already pending for this email')
                ->warning()
                ->send();

            return;
        }

        $invitation = StaffInvitation::query()->create([
            'email' => $this->inviteEmail,
            'role' => $this->inviteRole,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
            'invited_by' => Auth::id(),
        ]);

        $storeName = Setting::get('store_name', 'Our Bakery');
        $acceptUrl = route('invitation.show', $invitation->token);

        Mail::to($this->inviteEmail)->send(
            new StaffInvitationMail($invitation, $storeName, $acceptUrl)
        );

        $this->inviteEmail = '';
        $this->inviteRole = UserRole::Staff->value;

        Notification::make()
            ->title('Invitation sent!')
            ->success()
            ->send();
    }

    public function revokeInvitation(int $id): void
    {
        StaffInvitation::query()->where('id', $id)->whereNull('accepted_at')->delete();

        Notification::make()
            ->title('Invitation revoked')
            ->success()
            ->send();
    }

    public function changeRole(int $userId, string $newRole): void
    {
        if (! UserRole::tryFrom($newRole)) {
            return;
        }

        $user = User::query()->findOrFail($userId);

        // Can't change own role
        if ($user->id === Auth::id()) {
            Notification::make()
                ->title("You can't change your own role")
                ->warning()
                ->send();

            return;
        }

        $user->update(['role' => $newRole]);

        Notification::make()
            ->title("Role updated to {$newRole}")
            ->success()
            ->send();
    }

    public function removeMember(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        if ($user->id === Auth::id()) {
            Notification::make()
                ->title("You can't remove yourself")
                ->warning()
                ->send();

            return;
        }

        // Don't remove the last owner
        if ($user->isOwner() && User::query()->where('role', UserRole::Owner)->count() <= 1) {
            Notification::make()
                ->title("Can't remove the last owner")
                ->danger()
                ->send();

            return;
        }

        $user->delete();

        Notification::make()
            ->title('Team member removed')
            ->success()
            ->send();
    }
}
