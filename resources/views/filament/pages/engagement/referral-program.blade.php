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
    <div class="bg-brand-900 border-brand-800/60 mb-6 rounded-xl border p-6">
        <div class="flex flex-wrap items-start gap-4">
            <div class="min-w-0 flex-1">
                <div class="text-brand-300 mb-1 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                    Your Referral Link
                </div>
                <h2 class="mb-1 text-[1.1rem] leading-tight font-bold text-white">Earn 1 free month per referral</h2>
                <p class="text-brand-400 text-sm">
                    Share this link with fellow bakers. When they sign up and subscribe, you'll earn 1 free month.
                </p>
            </div>
        </div>

        <div class="mt-4 flex items-center gap-2">
            <input
                type="text"
                readonly
                value="{{ $this->getReferralLink() }}"
                class="bg-brand-800 border-brand-700/60 focus:border-brand-300/40 flex-1 rounded-lg border px-4 py-2 text-sm text-white focus:outline-none"
                id="referral-link"
            />
            <button
                type="button"
                onclick="
                    navigator.clipboard.writeText(document.getElementById('referral-link').value);
                    this.textContent = 'Copied';
                    setTimeout(() => (this.textContent = 'Copy'), 2000);
                "
                class="bg-brand-300 text-brand-900 inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-bold transition hover:opacity-90"
            >
                Copy
            </button>
        </div>

        <p class="text-brand-400 mt-2 text-xs">
            Code: <span class="text-brand-200 font-mono">{{ $this->getReferralCode() }}</span>
        </p>
    </div>

    {{-- ============== STATS ============== --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="bg-brand-800 border-brand-700/60 rounded-xl border p-5">
            <div class="text-brand-300 mb-2 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                Total Referrals
            </div>
            <div class="text-[1.75rem] leading-none font-bold text-white tabular-nums">
                {{ $this->getTotalReferrals() }}
            </div>
        </div>
        <div class="bg-brand-800 border-brand-700/60 rounded-xl border p-5">
            <div class="text-brand-300 mb-2 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Completed</div>
            <div class="text-[1.75rem] leading-none font-bold text-emerald-400 tabular-nums">
                {{ $this->getCompletedReferrals() }}
            </div>
        </div>
        <div class="bg-brand-800 border-brand-700/60 rounded-xl border p-5">
            <div class="text-brand-300 mb-2 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Months Earned</div>
            <div class="text-brand-300 text-[1.75rem] leading-none font-bold tabular-nums">
                {{ $this->getMonthsEarned() }}
            </div>
        </div>
    </div>

    {{-- ============== REFERRAL LIST ============== --}}
    <div class="bg-brand-800 border-brand-700/60 overflow-hidden rounded-xl border">
        <div class="border-brand-700/40 border-b px-6 py-3">
            <h3 class="text-sm font-semibold text-white">Your Referrals</h3>
        </div>

        @if ($referrals->isEmpty())
            <div class="text-brand-400 px-6 py-10 text-center text-sm">
                No referrals yet. Share your link to get started.
            </div>
        @else
            <ul role="list" class="divide-brand-700/40 divide-y">
                @foreach ($referrals as $referral)
                    @php $tone = $statusTone($referral->status); @endphp
                    <li class="flex items-center justify-between gap-4 px-6 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-white">
                                {{ $referral->referred?->store_name ?? $referral->referred_email ?? 'Unknown' }}
                            </p>
                            <p class="text-brand-400 mt-0.5 text-xs">{{ $referral->created_at->diffForHumans() }}</p>
                        </div>
                        <span @class([
                            'inline-flex items-center gap-1.5 text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1 border shrink-0',
                            $tone['bg'], $tone['border'], $tone['text'],
                        ])>
                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                            {{ ucfirst($referral->status) }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-filament-panels::page>
