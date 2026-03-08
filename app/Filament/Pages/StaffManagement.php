<?php

namespace App\Filament\Pages;

use App\Filament\Traits\RequiresRole;
use App\Mail\StaffInvitationMail;
use App\Models\Setting;
use App\Models\StaffInvitation;
use App\Models\User;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use UnitEnum;

class StaffManagement extends Page
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'owner';
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|UnitEnum|null $navigationGroup = 'Admin';

    protected static ?string $navigationLabel = 'Staff';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.staff-management';

    public string $inviteEmail = '';

    public string $inviteRole = 'staff';

    public static function canAccess(): bool
    {
        return Auth::user()?->isOwner() ?? false;
    }

    public function getTitle(): string
    {
        return 'Team Management';
    }

    public function getTeamMembers()
    {
        return User::orderByRaw("FIELD(role, 'owner', 'manager', 'staff')")->get();
    }

    public function getPendingInvitations()
    {
        return StaffInvitation::whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->get();
    }

    public function sendInvitation(): void
    {
        $this->validate([
            'inviteEmail' => 'required|email',
            'inviteRole' => 'required|in:manager,staff',
        ]);

        // Check if already a team member
        if (User::where('email', $this->inviteEmail)->exists()) {
            Notification::make()
                ->title('This person is already a team member')
                ->warning()
                ->send();

            return;
        }

        // Check for existing pending invitation
        $existing = StaffInvitation::where('email', $this->inviteEmail)
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

        $invitation = StaffInvitation::create([
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
        $this->inviteRole = 'staff';

        Notification::make()
            ->title('Invitation sent!')
            ->success()
            ->send();
    }

    public function revokeInvitation(int $id): void
    {
        StaffInvitation::where('id', $id)->whereNull('accepted_at')->delete();

        Notification::make()
            ->title('Invitation revoked')
            ->success()
            ->send();
    }

    public function changeRole(int $userId, string $newRole): void
    {
        if (! in_array($newRole, ['owner', 'manager', 'staff'])) {
            return;
        }

        $user = User::findOrFail($userId);

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
        $user = User::findOrFail($userId);

        if ($user->id === Auth::id()) {
            Notification::make()
                ->title("You can't remove yourself")
                ->warning()
                ->send();

            return;
        }

        // Don't remove the last owner
        if ($user->isOwner() && User::where('role', 'owner')->count() <= 1) {
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
