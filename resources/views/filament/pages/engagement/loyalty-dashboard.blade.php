@use(App\Enums\Engagement\LoyaltyPointType)

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Toggle --}}
        <div class="flex items-center justify-between rounded-xl bg-(--kn-surface) p-4 shadow-sm">
            <div>
                <h3 class="text-lg font-semibold">{{ $this->programName }} Program</h3>
                <p class="text-sm text-(--kn-muted)">
                    {{ $this->loyaltyEnabled ? 'Customers earn points on every delivered order' : 'Program is currently disabled' }}
                </p>
            </div>
            <button
                wire:click="toggleLoyalty"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $this->loyaltyEnabled ? 'bg-(--kn-honey)' : 'bg-gray-300' }}"
            >
                <span class="inline-block h-4 w-4 transform rounded-full bg-(--kn-surface) transition-transform {{ $this->loyaltyEnabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
            </button>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-(--kn-surface) p-4 shadow-sm">
                <p class="text-sm text-(--kn-muted)">Total Points Issued</p>
                <p class="text-2xl font-bold">{{ number_format($this->totalPointsIssued) }}</p>
            </div>
            <div class="rounded-xl bg-(--kn-surface) p-4 shadow-sm">
                <p class="text-sm text-(--kn-muted)">Total Points Redeemed</p>
                <p class="text-2xl font-bold">{{ number_format($this->totalPointsRedeemed) }}</p>
            </div>
            <div class="rounded-xl bg-(--kn-surface) p-4 shadow-sm">
                <p class="text-sm text-(--kn-muted)">Active Members</p>
                <p class="text-2xl font-bold">{{ number_format($this->activeMembers) }}</p>
            </div>
            <div class="rounded-xl bg-(--kn-surface) p-4 shadow-sm">
                <p class="text-sm text-(--kn-muted)">Available Rewards</p>
                <p class="text-2xl font-bold">{{ $this->availableRewardsCount }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Top Customers --}}
            <div class="overflow-hidden rounded-xl bg-(--kn-surface) shadow-sm">
                <div class="border-b p-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold">Top Customers by Points</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-(--kn-surface-sunken)">
                        <tr>
                            <th class="p-3 text-left">Customer</th>
                            <th class="p-3 text-right">Earned</th>
                            <th class="p-3 text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->topCustomers as $customer)
                            <tr class="border-t dark:border-gray-700">
                                <td class="p-3">{{ $customer->name }}</td>
                                <td class="p-3 text-right">{{ number_format($customer->total_earned) }}</td>
                                <td class="p-3 text-right font-semibold">{{ number_format($customer->balance) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-4 text-center text-(--kn-muted)">No loyalty activity yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Recent Activity --}}
            <div class="overflow-hidden rounded-xl bg-(--kn-surface) shadow-sm">
                <div class="border-b p-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold">Recent Activity</h3>
                </div>
                <div class="max-h-96 divide-y overflow-y-auto dark:divide-gray-700">
                    @forelse ($this->recentActivity as $activity)
                        <div class="flex items-center justify-between p-3">
                            <div>
                                <p class="font-medium">{{ $activity->customer?->name ?? 'Unknown' }}</p>
                                <p class="text-sm text-(--kn-muted)">{{ $activity->description }}</p>
                            </div>
                            <div class="text-right">
                                <span
                                    class="font-semibold {{
                                        match ($activity->type) {
                                            LoyaltyPointType::Earned => 'text-(--kn-success)',
                                            LoyaltyPointType::Redeemed, LoyaltyPointType::Reversed => 'text-(--kn-danger)',
                                            LoyaltyPointType::Adjusted => 'text-(--kn-warning)',
                                        }
                                    }}"
                                >
                                    {{ $activity->type->formatPoints($activity->points) }}
                                </span>
                                <p class="text-xs text-(--kn-muted)">{{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-(--kn-muted)">No activity yet</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
