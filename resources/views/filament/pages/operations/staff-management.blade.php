<x-filament-panels::page>
    {{-- Invite Form --}}
    <x-filament::section>
        <x-slot name="heading">Invite Team Member</x-slot>
        <form wire:submit="sendInvitation" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input type="email" wire:model="inviteEmail" required placeholder="staff@example.com"
                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
            </div>
            <div class="w-full sm:w-48">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                <select wire:model="inviteRole"
                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                    <option value="staff">Staff</option>
                    <option value="manager">Manager</option>
                </select>
            </div>
            <x-filament::button type="submit" icon="heroicon-o-paper-airplane">
                Send Invite
            </x-filament::button>
        </form>
    </x-filament::section>

    {{-- Pending Invitations --}}
    @php $invitations = $this->getPendingInvitations(); @endphp
    @if ($invitations->count() > 0)
        <x-filament::section>
            <x-slot name="heading">Pending Invitations</x-slot>
            <div class="divide-y dark:divide-gray-700">
                @foreach ($invitations as $invitation)
                    <div class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $invitation->email }}</p>
                            <p class="text-sm text-gray-500">
                                {{ ucfirst($invitation->role instanceof \BackedEnum ? $invitation->role->value : $invitation->role) }} · Expires {{ $invitation->expires_at->diffForHumans() }}
                            </p>
                        </div>
                        <x-filament::button color="danger" size="sm" wire:click="revokeInvitation({{ $invitation->id }})">
                            Revoke
                        </x-filament::button>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endif

    {{-- Team Members --}}
    <x-filament::section>
        <x-slot name="heading">Team Members</x-slot>
        <div class="divide-y dark:divide-gray-700">
            @foreach ($this->getTeamMembers() as $member)
                <div class="flex items-center justify-between py-3">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $member->name }}</p>
                        <p class="text-sm text-gray-500">{{ $member->email }} · Joined {{ $member->created_at->format('M j, Y') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if ($member->id !== auth()->id())
                            <select wire:change="changeRole({{ $member->id }}, $event.target.value)"
                                class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="owner" @selected($member->role === 'owner')>Owner</option>
                                <option value="manager" @selected($member->role === 'manager')>Manager</option>
                                <option value="staff" @selected($member->role === 'staff')>Staff</option>
                            </select>
                            <x-filament::button color="danger" size="sm" wire:click="removeMember({{ $member->id }})"
                                wire:confirm="Are you sure you want to remove this team member?">
                                Remove
                            </x-filament::button>
                        @else
                            <span class="inline-flex items-center rounded-full bg-primary-50 px-3 py-1 text-sm font-medium text-primary-700 dark:bg-primary-400/10 dark:text-primary-400">
                                {{ ucfirst($member->role instanceof \BackedEnum ? $member->role->value : $member->role) }} (You)
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-panels::page>
