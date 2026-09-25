@php
    use App\Enums\Customers\CateringInquiryStatus;

    $inquiry = $record;
    $status = $inquiry->status;
    $order = $inquiry->order;

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
    <div class="bg-brand-900 border-brand-800/60 mb-6 flex flex-col gap-5 rounded-xl border p-6 md:flex-row md:items-center">
        <div class="min-w-0 flex-1">
            <div class="text-brand-300 mb-1 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                Catering Inquiry
            </div>
            <h2 class="truncate text-[1.35rem] leading-tight font-bold text-white">{{ $inquiry->customer_name }}</h2>
            <div class="text-brand-400 mt-1 text-[0.85rem]">
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

        <div class="flex flex-wrap items-center gap-2">
            <span class="bg-brand-800 border-brand-700 text-brand-200 inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] uppercase">
                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                {{ $status->getLabel() }}
            </span>
            @if ($depositChip)
                <span class="inline-flex items-center gap-1.5 {{ $depositChip['bg'] }} border {{ $depositChip['border'] }} {{ $depositChip['text'] }} text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                    {{ $depositChip['label'] }}
                </span>
            @endif
        </div>

        <div class="shrink-0 text-right">
            <div class="text-brand-300 mb-0.5 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Quoted</div>
            <div class="text-[1.5rem] leading-none font-bold text-white tabular-nums">
                {{ $inquiry->quoted_amount?->formatted() ?? '—' }}
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- ============== CUSTOMER ============== --}}
        <x-filament.catering-inquiries.section-card title="Customer">
            <x-slot:actions>{{ $this->editCustomerAction }}</x-slot:actions>

            <dl class="divide-brand-700/40 divide-y">
                <x-filament.catering-inquiries.detail-row label="Name" class="truncate">
                    {{ $inquiry->customer_name }}
                </x-filament.catering-inquiries.detail-row>
                @if ($inquiry->customer_email)
                    <x-filament.catering-inquiries.detail-row label="Email" class="truncate">
                        {{ $inquiry->customer_email }}
                    </x-filament.catering-inquiries.detail-row>
                @endif
                @if ($inquiry->customer_phone)
                    <x-filament.catering-inquiries.detail-row label="Phone">
                        {{ $inquiry->customer_phone }}
                    </x-filament.catering-inquiries.detail-row>
                @endif
            </dl>
        </x-filament.catering-inquiries.section-card>

        {{-- ============== EVENT DETAILS ============== --}}
        <x-filament.catering-inquiries.section-card title="Event details">
            <x-slot:actions>{{ $this->editEventDetailsAction }}</x-slot:actions>

            <dl class="divide-brand-700/40 divide-y">
                <x-filament.catering-inquiries.detail-row label="Type">
                    {{ $inquiry->event_type }}
                </x-filament.catering-inquiries.detail-row>
                <x-filament.catering-inquiries.detail-row label="Date">
                    {{ $eventDate?->format('M j, Y') ?? '—' }}
                </x-filament.catering-inquiries.detail-row>
                <x-filament.catering-inquiries.detail-row label="Guests" class="tabular-nums">
                    {{ number_format($inquiry->guest_count) }}
                </x-filament.catering-inquiries.detail-row>
                @if ($inquiry->budget)
                    <x-filament.catering-inquiries.detail-row label="Budget" class="tabular-nums">
                        {{ $inquiry->budget->formatted() }}
                    </x-filament.catering-inquiries.detail-row>
                @endif
                @if ($inquiry->venue_address)
                    <x-filament.catering-inquiries.detail-row label="Venue" class="max-w-md whitespace-pre-wrap">
                        {{ $inquiry->venue_address }}
                    </x-filament.catering-inquiries.detail-row>
                @endif
                @if ($inquiry->dietary_requirements)
                    <x-filament.catering-inquiries.detail-row label="Dietary" class="max-w-md whitespace-pre-wrap">
                        {{ $inquiry->dietary_requirements }}
                    </x-filament.catering-inquiries.detail-row>
                @endif
                @if ($inquiry->details)
                    <x-filament.catering-inquiries.detail-row label="Details" class="max-w-md whitespace-pre-wrap">
                        {{ $inquiry->details }}
                    </x-filament.catering-inquiries.detail-row>
                @endif
            </dl>
        </x-filament.catering-inquiries.section-card>

        {{-- ============== QUOTE ============== --}}
        <x-filament.catering-inquiries.section-card
            title="Quote"
            header-class="mb-4 flex flex-wrap items-center justify-between gap-3"
        >
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($this->manageQuoteItemsAction->isVisible())
                        {{ $this->manageQuoteItemsAction }}
                    @endif
                    @if ($this->sendQuoteAction->isVisible())
                        {{ $this->sendQuoteAction }}
                    @endif
                    @if ($this->resendQuoteAction->isVisible())
                        {{ $this->resendQuoteAction }}
                    @endif
                </div>
            </x-slot:actions>

            <x-filament.catering-inquiries.quote-content
                :items="$inquiry->items"
                :status="$status"
                :quoted-amount="$inquiry->quoted_amount"
                :can-manage-items="$this->manageQuoteItemsAction->isVisible()"
            />
        </x-filament.catering-inquiries.section-card>

        {{-- ============== BOOKING ============== --}}
        <x-filament.catering-inquiries.section-card
            title="Booking"
            header-class="mb-4 flex flex-wrap items-center justify-between gap-3"
        >
            <x-slot:actions>
                @if ($this->confirmBookingAction->isVisible())
                    {{ $this->confirmBookingAction }}
                @endif
            </x-slot:actions>

            @if ($order)
                <a
                    href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $order]) }}"
                    class="bg-brand-800 border-brand-700/60 hover:border-brand-300/40 group -mx-2 flex items-center justify-between gap-4 rounded-lg border px-4 py-3 transition-colors"
                >
                    <div class="min-w-0">
                        <div class="text-brand-300 mb-0.5 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                            Linked order
                        </div>
                        <div class="font-mono text-[0.95rem] font-bold text-white">{{ $order->order_number }}</div>
                        <div class="text-brand-400 mt-0.5 text-[0.8rem]">
                            {{ $order->status->getLabel() }} · {{ $order->payment_status->getLabel() }} · {{ $order->total->formatted() }}
                        </div>
                    </div>
                    <x-heroicon-o-arrow-top-right-on-square class="text-brand-400 group-hover:text-brand-200 h-4 w-4 shrink-0 transition-colors" />
                </a>
            @else
                <div class="text-brand-200 text-[0.9rem]">
                    @if ($status === CateringInquiryStatus::Quoted)
                        Awaiting confirmation. Confirming creates an order so the rest of fulfillment (payment,
                        messages, status) is tracked there.
                    @elseif ($status === CateringInquiryStatus::Cancelled)
                        <span class="font-semibold text-red-400">Cancelled.</span>
                    @else
                        Send a quote first; confirmation becomes available once the customer has been quoted.
                    @endif
                </div>
            @endif
        </x-filament.catering-inquiries.section-card>

        {{-- ============== DEPOSIT ============== --}}
        <x-filament.catering-inquiries.section-card
            title="Deposit"
            header-class="mb-4 flex flex-wrap items-center justify-between gap-3"
        >
            <x-slot:actions>
                @if ($this->markDepositReceivedAction->isVisible())
                    {{ $this->markDepositReceivedAction }}
                @endif
            </x-slot:actions>

            @if ($depositPaid)
                <dl class="divide-brand-700/40 divide-y">
                    <x-filament.catering-inquiries.detail-row label="Amount" class="tabular-nums">
                        {{ $inquiry->deposit_amount?->formatted() ?? '—' }}
                    </x-filament.catering-inquiries.detail-row>
                    <x-filament.catering-inquiries.detail-row label="Received">
                        {{ $inquiry->deposit_paid_at->format('M j, Y') }}
                    </x-filament.catering-inquiries.detail-row>
                    @if ($inquiry->deposit_reference)
                        <x-filament.catering-inquiries.detail-row label="Reference">
                            {{ $inquiry->deposit_reference }}
                        </x-filament.catering-inquiries.detail-row>
                    @endif
                </dl>
                @if ($order)
                    <div class="text-brand-400 border-brand-700/40 mt-3 border-t pt-3 text-[0.8rem]">
                        Balance is tracked on the linked order.
                    </div>
                @endif
            @else
                <div class="text-brand-200 text-[0.9rem]">
                    Not received.
                    @if ($suggestedDeposit !== null)
                        Suggested deposit:
                        <span class="font-semibold text-white tabular-nums">${{ number_format($suggestedDeposit, 2) }}</span>
                        <span class="text-brand-400">({{ $depositPercent }}% of quote)</span>
                    @endif
                </div>
            @endif
        </x-filament.catering-inquiries.section-card>

        {{-- ============== INTERNAL NOTES ============== --}}
        <x-filament.catering-inquiries.section-card title="Internal notes">
            <x-slot:actions>{{ $this->editNotesAction }}</x-slot:actions>

            @if (filled($inquiry->notes))
                <pre class="text-brand-200 m-0 font-sans text-[0.85rem] leading-relaxed whitespace-pre-wrap">{{ $inquiry->notes }}</pre>
            @else
                <div class="text-brand-400 text-[0.85rem]">No notes yet.</div>
            @endif
        </x-filament.catering-inquiries.section-card>
    </div>
</x-filament-panels::page>
