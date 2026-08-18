@php
    $referrals = $this->getReferrals();
    $statusTone = fn (string $status): array => match ($status) {
        'pending' => ['bg' => 'bg-amber-500/15', 'border' => 'border-amber-500/25', 'text' => 'text-amber-400'],
        'completed' => ['bg' => 'bg-emerald-500/15', 'border' => 'border-emerald-500/25', 'text' => 'text-emerald-400'],
        'rewarded' => ['bg' => 'bg-sky-500/15', 'border' => 'border-sky-500/25', 'text' => 'text-sky-400'],
        default => ['bg' => 'bg-brand-300/15', 'border' => 'border-brand-300/25', 'text' => 'text-brand-300'],
    };
@endphp

<x-filament-panels::page>
    {{-- ============== HERO STRIP ============== --}}
    <div class="mb-6 bg-brand-900 border border-brand-800/60 rounded-xl p-6">
        <div class="flex items-start gap-4 flex-wrap">
            <div class="min-w-0 flex-1">
                <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-1">Your Referral Link</div>
                <h2 class="text-white text-[1.1rem] font-bold leading-tight mb-1">Earn 1 free month per referral</h2>
                <p class="text-brand-400 text-sm">Share this link with fellow bakers. When they sign up and subscribe, you'll earn 1 free month.</p>
            </div>
        </div>

        <div class="flex items-center gap-2 mt-4">
            <input
                type="text"
                readonly
                value="{{ $this->getReferralLink() }}"
                class="flex-1 rounded-lg bg-brand-800 border border-brand-700/60 px-4 py-2 text-sm text-white focus:outline-none focus:border-brand-300/40"
                id="referral-link"
            />
            <button
                type="button"
                onclick="navigator.clipboard.writeText(document.getElementById('referral-link').value); this.textContent = 'Copied'; setTimeout(() => this.textContent = 'Copy', 2000);"
                class="inline-flex items-center justify-center rounded-lg bg-brand-300 text-brand-900 px-4 py-2 text-sm font-bold hover:opacity-90 transition"
            >
                Copy
            </button>
        </div>

        <p class="text-brand-400 text-xs mt-2">
            Code: <span class="font-mono text-brand-200">{{ $this->getReferralCode() }}</span>
        </p>
    </div>

    {{-- ============== STATS ============== --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <div class="bg-brand-800 border border-brand-700/60 rounded-xl p-5">
            <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-2">Total Referrals</div>
            <div class="text-white text-[1.75rem] font-bold leading-none tabular-nums">{{ $this->getTotalReferrals() }}</div>
        </div>
        <div class="bg-brand-800 border border-brand-700/60 rounded-xl p-5">
            <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-2">Completed</div>
            <div class="text-emerald-400 text-[1.75rem] font-bold leading-none tabular-nums">{{ $this->getCompletedReferrals() }}</div>
        </div>
        <div class="bg-brand-800 border border-brand-700/60 rounded-xl p-5">
            <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-2">Months Earned</div>
            <div class="text-brand-300 text-[1.75rem] font-bold leading-none tabular-nums">{{ $this->getMonthsEarned() }}</div>
        </div>
    </div>

    {{-- ============== REFERRAL LIST ============== --}}
    <div class="bg-brand-800 border border-brand-700/60 rounded-xl overflow-hidden">
        <div class="border-b border-brand-700/40 px-6 py-3">
            <h3 class="text-white text-sm font-semibold">Your Referrals</h3>
        </div>

        @if ($referrals->isEmpty())
            <div class="px-6 py-10 text-center text-sm text-brand-400">
                No referrals yet. Share your link to get started.
            </div>
        @else
            <ul role="list" class="divide-y divide-brand-700/40">
                @foreach ($referrals as $referral)
                    @php $tone = $statusTone($referral->status); @endphp
                    <li class="flex items-center justify-between gap-4 px-6 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-white">
                                {{ $referral->referred?->store_name ?? $referral->referred_email ?? 'Unknown' }}
                            </p>
                            <p class="text-xs text-brand-400 mt-0.5">{{ $referral->created_at->diffForHumans() }}</p>
                        </div>
                        <span @class([
                            'inline-flex items-center gap-1.5 text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1 border shrink-0',
                            $tone['bg'], $tone['border'], $tone['text'],
                        ])>
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ ucfirst($referral->status) }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-filament-panels::page>
