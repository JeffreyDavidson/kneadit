@php
    use App\Enums\Staff\UserRole;

    $currentUserId = auth()->id();
    $invitations = $this->getPendingInvitations();
    $members = $this->getTeamMembers();

    $initials = function (string $name): string {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = $parts[0] ?? '';
        $last = count($parts) > 1 ? end($parts) : '';

        return strtoupper(mb_substr($first, 0, 1) . mb_substr($last, 0, 1));
    };

    $roleColor = fn (UserRole $role): string => match ($role) {
        UserRole::Owner => 'warning',
        UserRole::Manager => 'info',
        UserRole::Staff => 'gray',
        default => 'gray',
    };
@endphp

<x-filament-panels::page>
    {{-- Pending Invitations --}}
    @if ($invitations->count() > 0)
        <x-filament::section>
            <x-slot name="heading">Pending Invitations</x-slot>
            <x-slot name="description">{{ $invitations->count() }} {{ Str::plural('invitation', $invitations->count()) }} awaiting acceptance.</x-slot>

            <ul role="list" class="divide-y divide-brand-700/40">
                @foreach ($invitations as $invitation)
                    @php
                        $invitationRole = $invitation->role instanceof UserRole
                            ? $invitation->role
                            : (UserRole::tryFrom($invitation->role) ?? UserRole::Staff);
                    @endphp
                    <li class="flex items-center justify-between gap-4 py-3">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-500/15 text-amber-400">
                                <x-heroicon-o-clock class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-medium text-white">{{ $invitation->email }}</p>
                                <p class="mt-1 truncate text-sm text-brand-400">
                                    Expires {{ $invitation->expires_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <x-filament::badge :color="$roleColor($invitationRole)">
                                {{ $invitationRole->getLabel() }}
                            </x-filament::badge>
                            {{ ($this->revokeInvitationAction)(['invitation' => $invitation->id]) }}
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-filament::section>
    @endif

    {{-- Team Members --}}
    <x-filament::section>
        <x-slot name="heading">Team Members</x-slot>
        <x-slot name="description">{{ $members->count() }} active {{ Str::plural('member', $members->count()) }}.</x-slot>

        <ul role="list" class="divide-y divide-brand-700/40">
            @foreach ($members as $member)
                <li class="flex items-center justify-between gap-4 py-3">
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-700 text-sm font-semibold text-brand-200">
                            {{ $initials($member->name) }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate font-medium text-white">{{ $member->name }}</p>
                            <p class="mt-1 truncate text-sm text-brand-400">
                                {{ $member->email }} · Joined {{ $member->created_at->format('M j, Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-3">
                        <x-filament::badge :color="$roleColor($member->role)">
                            {{ $member->role->getLabel() }}@if ($member->id === $currentUserId) · You @endif
                        </x-filament::badge>

                        @if ($member->id !== $currentUserId)
                            {{ ($this->changeRoleAction)(['user' => $member->id]) }}
                            {{ ($this->removeMemberAction)(['user' => $member->id]) }}
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </x-filament::section>
</x-filament-panels::page>
