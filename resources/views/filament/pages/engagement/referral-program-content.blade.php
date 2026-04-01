<div class="space-y-6">
    {{-- Referral Link --}}
    <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">🎁 Your Referral Link</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Share this link with fellow bakers. When they sign up and subscribe, you'll earn 1 free month!</p>
        <div class="flex items-center gap-2">
            <input
                type="text"
                readonly
                value="{{ $this->getReferralLink() }}"
                class="flex-1 rounded-lg border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200"
                id="referral-link"
            />
            <button
                onclick="navigator.clipboard.writeText(document.getElementById('referral-link').value); this.textContent = '✓ Copied!'; setTimeout(() => this.textContent = 'Copy', 2000);"
                class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 transition"
            >
                Copy
            </button>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Code: <span class="font-mono">{{ $this->getReferralCode() }}</span></p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Referrals</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->getTotalReferrals() }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Completed</p>
            <p class="text-2xl font-bold text-green-600">{{ $this->getCompletedReferrals() }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Months Earned</p>
            <p class="text-2xl font-bold text-primary-600">{{ $this->getMonthsEarned() }}</p>
        </div>
    </div>

    {{-- Referral List --}}
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Your Referrals</h3>
        </div>
        @php $referrals = $this->getReferrals(); @endphp
        @if ($referrals->isEmpty())
            <div class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                No referrals yet. Share your link to get started!
            </div>
        @else
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($referrals as $referral)
                    <div class="flex items-center justify-between px-6 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $referral->referred?->store_name ?? $referral->referred_email ?? 'Unknown' }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $referral->created_at->diffForHumans() }}</p>
                        </div>
                        <span @class([
                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                            'bg-yellow-100 text-yellow-800' => $referral->status === 'pending',
                            'bg-green-100 text-green-800' => $referral->status === 'completed',
                            'bg-blue-100 text-blue-800' => $referral->status === 'rewarded',
                        ])>
                            {{ ucfirst($referral->status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
