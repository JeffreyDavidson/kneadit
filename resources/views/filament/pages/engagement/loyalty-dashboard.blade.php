<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Toggle --}}
        <div class="flex items-center justify-between p-4 bg-white rounded-xl shadow-sm dark:bg-gray-800">
            <div>
                <h3 class="text-lg font-semibold">{{ $this->programName }} Program</h3>
                <p class="text-sm text-gray-500">{{ $this->loyaltyEnabled ? 'Customers earn points on every delivered order' : 'Program is currently disabled' }}</p>
            </div>
            <button
                wire:click="toggleLoyalty"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $this->loyaltyEnabled ? 'bg-primary-600' : 'bg-gray-300' }}"
            >
                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $this->loyaltyEnabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
            </button>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <p class="text-sm text-gray-500">Total Points Issued</p>
                <p class="text-2xl font-bold">{{ number_format($this->totalPointsIssued) }}</p>
            </div>
            <div class="p-4 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <p class="text-sm text-gray-500">Total Points Redeemed</p>
                <p class="text-2xl font-bold">{{ number_format($this->totalPointsRedeemed) }}</p>
            </div>
            <div class="p-4 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <p class="text-sm text-gray-500">Active Members</p>
                <p class="text-2xl font-bold">{{ number_format($this->activeMembers) }}</p>
            </div>
            <div class="p-4 bg-white rounded-xl shadow-sm dark:bg-gray-800">
                <p class="text-sm text-gray-500">Available Rewards</p>
                <p class="text-2xl font-bold">{{ $this->availableRewardsCount }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Top Customers --}}
            <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 overflow-hidden">
                <div class="p-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold">Top Customers by Points</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="text-left p-3">Customer</th>
                            <th class="text-right p-3">Earned</th>
                            <th class="text-right p-3">Balance</th>
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
                        <tr><td colspan="3" class="p-4 text-center text-gray-500">No loyalty activity yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Recent Activity --}}
            <div class="bg-white rounded-xl shadow-sm dark:bg-gray-800 overflow-hidden">
                <div class="p-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold">Recent Activity</h3>
                </div>
                <div class="divide-y dark:divide-gray-700 max-h-96 overflow-y-auto">
                    @forelse ($this->recentActivity as $activity)
                    <div class="p-3 flex items-center justify-between">
                        <div>
                            <p class="font-medium">{{ $activity->customer?->name ?? 'Unknown' }}</p>
                            <p class="text-sm text-gray-500">{{ $activity->description }}</p>
                        </div>
                        <div class="text-right">
                            <span class="font-semibold {{ $activity->type === 'earned' ? 'text-green-600' : ($activity->type === 'redeemed' ? 'text-red-600' : 'text-yellow-600') }}">
                                {{ $activity->type === 'redeemed' ? '-' : '+' }}{{ number_format($activity->points) }}
                            </span>
                            <p class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 text-center text-gray-500">No activity yet</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
