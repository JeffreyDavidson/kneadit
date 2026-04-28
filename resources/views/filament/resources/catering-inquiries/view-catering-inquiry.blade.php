@php
    use App\Enums\Customers\CateringInquiryStatus;

    $inquiry = $record;
    $status = $inquiry->status;

    $eventDate = $inquiry->event_date;
    $eventCountdown = $eventDate?->isFuture() ? $eventDate->diffForHumans(['parts' => 1, 'short' => false]) : null;
    $eventPast = $eventDate?->isPast() ?? false;

    $depositPaid = $inquiry->deposit_paid_at !== null;
    $depositPercent = resolve(\App\Services\Settings\TenantSettings::class)->catering->depositPercent;
    $suggestedDeposit = $inquiry->quoted_amount && $depositPercent > 0
        ? round($inquiry->quoted_amount->dollars() * (min(100, $depositPercent) / 100), 2)
        : null;

    $depositChip = match (true) {
        $depositPaid => ['label' => 'Deposit received', 'bg' => 'bg-emerald-500/15', 'border' => 'border-emerald-500/25', 'text' => 'text-emerald-400'],
        in_array($status, [CateringInquiryStatus::Quoted, CateringInquiryStatus::Confirmed], true) => ['label' => 'Deposit pending', 'bg' => 'bg-amber-500/15', 'border' => 'border-amber-500/25', 'text' => 'text-amber-400'],
        default => null,
    };
@endphp

<x-filament-panels::page>
    {{-- ============== HERO STRIP ============== --}}
    <div class="mb-6 bg-brand-900 border border-brand-800/60 rounded-xl p-6 flex flex-col md:flex-row md:items-center gap-5">
        <div class="flex-1 min-w-0">
            <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-1">Catering Inquiry</div>
            <h2 class="text-white text-[1.35rem] font-bold leading-tight truncate">{{ $inquiry->customer_name }}</h2>
            <div class="text-brand-400 text-[0.85rem] mt-1">
                <span class="text-brand-200 font-semibold">{{ $inquiry->event_type }}</span>
                @if ($eventDate)
                    · {{ $eventDate->format('M j, Y') }}
                    @if ($eventCountdown)
                        <span class="text-brand-400">(in {{ $eventCountdown }})</span>
                    @elseif ($eventPast)
                        <span class="text-brand-400">(past)</span>
                    @endif
                @endif
                · {{ number_format($inquiry->guest_count) }} guests
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1.5 bg-brand-800 border border-brand-700 text-brand-200 text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                {{ $status->getLabel() }}
            </span>
            @if ($depositChip)
                <span class="inline-flex items-center gap-1.5 {{ $depositChip['bg'] }} border {{ $depositChip['border'] }} {{ $depositChip['text'] }} text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                    {{ $depositChip['label'] }}
                </span>
            @endif
        </div>

        <div class="text-right shrink-0">
            <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-0.5">Quoted</div>
            <div class="text-white text-[1.5rem] font-bold leading-none tabular-nums">{{ $inquiry->quoted_amount?->formatted() ?? '—' }}</div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- ============== CUSTOMER ============== --}}
        <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold">Customer</div>
                {{ $this->editCustomerAction }}
            </div>
            <dl class="divide-y divide-brand-700/40">
                <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                    <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Name</dt>
                    <dd class="text-white text-[0.85rem] font-semibold text-right truncate">{{ $inquiry->customer_name }}</dd>
                </div>
                @if ($inquiry->customer_email)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Email</dt>
                        <dd class="text-white text-[0.85rem] font-semibold text-right truncate">{{ $inquiry->customer_email }}</dd>
                    </div>
                @endif
                @if ($inquiry->customer_phone)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Phone</dt>
                        <dd class="text-white text-[0.85rem] font-semibold text-right">{{ $inquiry->customer_phone }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- ============== EVENT DETAILS ============== --}}
        <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold">Event details</div>
                {{ $this->editEventDetailsAction }}
            </div>
            <dl class="divide-y divide-brand-700/40">
                <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                    <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Type</dt>
                    <dd class="text-white text-[0.85rem] font-semibold text-right">{{ $inquiry->event_type }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                    <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Date</dt>
                    <dd class="text-white text-[0.85rem] font-semibold text-right">{{ $eventDate?->format('M j, Y') ?? '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                    <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Guests</dt>
                    <dd class="text-white text-[0.85rem] font-semibold text-right tabular-nums">{{ number_format($inquiry->guest_count) }}</dd>
                </div>
                @if ($inquiry->budget)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Budget</dt>
                        <dd class="text-white text-[0.85rem] font-semibold text-right tabular-nums">{{ $inquiry->budget->formatted() }}</dd>
                    </div>
                @endif
                @if ($inquiry->venue_address)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Venue</dt>
                        <dd class="text-white text-[0.85rem] font-semibold text-right whitespace-pre-wrap max-w-md">{{ $inquiry->venue_address }}</dd>
                    </div>
                @endif
                @if ($inquiry->dietary_requirements)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Dietary</dt>
                        <dd class="text-white text-[0.85rem] font-semibold text-right whitespace-pre-wrap max-w-md">{{ $inquiry->dietary_requirements }}</dd>
                    </div>
                @endif
                @if ($inquiry->details)
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Details</dt>
                        <dd class="text-white text-[0.85rem] font-semibold text-right whitespace-pre-wrap max-w-md">{{ $inquiry->details }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- ============== QUOTE ============== --}}
        <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
                <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold">Quote</div>
                <div class="flex items-center gap-2 flex-wrap">
                    {{ $this->reviseQuoteAction }}
                    {{ $this->sendQuoteAction }}
                    {{ $this->resendQuoteAction }}
                </div>
            </div>

            @if ($inquiry->quoted_amount)
                <div class="flex items-baseline gap-3">
                    <div class="text-white text-[1.75rem] font-bold tabular-nums">{{ $inquiry->quoted_amount->formatted() }}</div>
                    <div class="text-brand-400 text-[0.85rem]">
                        @if ($status === CateringInquiryStatus::Inquiry)
                            Not yet sent
                        @else
                            Sent · status: {{ $status->getLabel() }}
                        @endif
                    </div>
                </div>
            @else
                <div class="text-brand-400 text-[0.9rem]">No amount set yet. Use <span class="text-brand-200 font-semibold">Revise amount</span> to enter a quote.</div>
            @endif
        </div>

        {{-- ============== BOOKING ============== --}}
        <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
                <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold">Booking</div>
                {{ $this->confirmBookingAction }}
            </div>

            <div class="text-brand-200 text-[0.9rem]">
                @if (in_array($status, [CateringInquiryStatus::Confirmed, CateringInquiryStatus::Completed], true))
                    <span class="text-emerald-400 font-semibold">Confirmed.</span>
                @elseif ($status === CateringInquiryStatus::Quoted)
                    Awaiting confirmation. Confirm once the customer agrees to the quote.
                @elseif ($status === CateringInquiryStatus::Cancelled)
                    <span class="text-red-400 font-semibold">Cancelled.</span>
                @else
                    Send a quote first; confirmation becomes available once the customer has been quoted.
                @endif
            </div>
        </div>

        {{-- ============== DEPOSIT ============== --}}
        <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
                <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold">Deposit</div>
                {{ $this->markDepositReceivedAction }}
            </div>

            @if ($depositPaid)
                <dl class="divide-y divide-brand-700/40">
                    <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                        <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Amount</dt>
                        <dd class="text-white text-[0.85rem] font-semibold text-right tabular-nums">{{ $inquiry->deposit_amount?->formatted() ?? '—' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                        <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Received</dt>
                        <dd class="text-white text-[0.85rem] font-semibold text-right">{{ $inquiry->deposit_paid_at->format('M j, Y') }}</dd>
                    </div>
                    @if ($inquiry->deposit_reference)
                        <div class="flex items-start justify-between gap-4 py-2.5 last:pb-0">
                            <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">Reference</dt>
                            <dd class="text-white text-[0.85rem] font-semibold text-right">{{ $inquiry->deposit_reference }}</dd>
                        </div>
                    @endif
                </dl>
            @else
                <div class="text-brand-200 text-[0.9rem]">
                    Not received.
                    @if ($suggestedDeposit !== null)
                        Suggested deposit: <span class="text-white font-semibold tabular-nums">${{ number_format($suggestedDeposit, 2) }}</span>
                        <span class="text-brand-400">({{ $depositPercent }}% of quote)</span>
                    @endif
                </div>
            @endif
        </div>

        {{-- ============== INTERNAL NOTES ============== --}}
        <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold">Internal notes</div>
                {{ $this->editNotesAction }}
            </div>

            @if (filled($inquiry->notes))
                <pre class="text-brand-200 text-[0.85rem] leading-relaxed whitespace-pre-wrap font-sans m-0">{{ $inquiry->notes }}</pre>
            @else
                <div class="text-brand-400 text-[0.85rem]">No notes yet.</div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
