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
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
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

    protected function getHeaderActions(): array
    {
        return [
            $this->inviteAction(),
        ];
    }

    public function inviteAction(): Action
    {
        return Action::make('invite')
            ->label('Invite Team Member')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('primary')
            ->slideOver()
            ->modalHeading('Invite Team Member')
            ->modalDescription('Send an email invitation to add staff or a manager to your team.')
            ->modalSubmitActionLabel('Send Invite')
            ->fillForm(fn (): array => [
                'email' => '',
                'role' => UserRole::Staff->value,
            ])
            ->schema([
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->placeholder('staff@example.com')
                    ->maxLength(255),
                Select::make('role')
                    ->label('Role')
                    ->options([
                        UserRole::Staff->value => 'Staff',
                        UserRole::Manager->value => 'Manager',
                    ])
                    ->required()
                    ->native(false),
            ])
            ->action(function (array $data): void {
                try {
                    resolve(SendStaffInvitation::class)(
                        email: $data['email'],
                        role: UserRole::from($data['role']),
                        invitedBy: (int) Auth::id(),
                    );

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
            });
    }

    public function changeRoleAction(): Action
    {
        return Action::make('changeRole')
            ->label('Change role')
            ->icon(Heroicon::OutlinedUserCircle)
            ->color('gray')
            ->size('sm')
            ->slideOver()
            ->modalHeading(function (array $arguments): string {
                $user = User::query()->findOrFail($arguments['user']);

                return "Change role for {$user->name}";
            })
            ->modalDescription('Promoting to Owner gives full billing and team access. Demotions take effect immediately.')
            ->modalSubmitActionLabel('Update role')
            ->fillForm(function (array $arguments): array {
                $user = User::query()->findOrFail($arguments['user']);

                return ['role' => $user->role->value];
            })
            ->schema(fn (): array => [
                Select::make('role')
                    ->label('Role')
                    ->options([
                        UserRole::Owner->value => 'Owner',
                        UserRole::Manager->value => 'Manager',
                        UserRole::Staff->value => 'Staff',
                    ])
                    ->required()
                    ->native(false),
            ])
            ->action(function (array $data, array $arguments): void {
                $role = UserRole::tryFrom($data['role']);

                if (! $role) {
                    return;
                }

                try {
                    resolve(ChangeStaffRole::class)(
                        userId: (int) $arguments['user'],
                        newRole: $role,
                        currentUserId: (int) Auth::id(),
                    );

                    Notification::make()
                        ->title("Role updated to {$role->getLabel()}")
                        ->success()
                        ->send();
                } catch (\RuntimeException $e) {
                    Notification::make()
                        ->title($e->getMessage())
                        ->warning()
                        ->send();
                }
            });
    }

    public function removeMemberAction(): Action
    {
        return Action::make('removeMember')
            ->label('Remove')
            ->icon(Heroicon::OutlinedTrash)
            ->color('danger')
            ->size('sm')
            ->requiresConfirmation()
            ->modalHeading(function (array $arguments): string {
                $user = User::query()->findOrFail($arguments['user']);

                return "Remove {$user->name}?";
            })
            ->modalDescription('This removes the team member immediately. They will lose access to the admin panel and any in-progress work assigned to them stays with the data, not the user.')
            ->modalSubmitActionLabel('Remove')
            ->action(function (array $arguments): void {
                try {
                    resolve(RemoveStaffMember::class)(
                        userId: (int) $arguments['user'],
                        currentUserId: (int) Auth::id(),
                    );

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
            });
    }

    public function revokeInvitationAction(): Action
    {
        return Action::make('revokeInvitation')
            ->label('Revoke')
            ->color('gray')
            ->size('sm')
            ->requiresConfirmation()
            ->modalHeading('Revoke this invitation?')
            ->modalDescription('The invite link will stop working. You can always send a fresh invitation.')
            ->modalSubmitActionLabel('Revoke')
            ->action(function (array $arguments): void {
                resolve(RevokeStaffInvitation::class)((int) $arguments['invitation']);

                Notification::make()
                    ->title('Invitation revoked')
                    ->success()
                    ->send();
            });
    }
}
